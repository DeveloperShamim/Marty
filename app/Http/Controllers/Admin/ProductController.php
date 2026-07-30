<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'images')->latest();

        if ($term = trim((string) $request->input('q'))) {
            $query->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        return view('admin.products.index', [
            'products'   => $query->paginate(15)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'q'          => $term,
            'category'   => $request->input('category'),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product'    => new Product(['is_published' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        unset($data['spec_labels'], $data['spec_values']);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name']);
        $this->applyFlags($request, $data, null);
        $data['specifications'] = $this->normalizeSpecifications($request);

        $product = Product::create($data);
        $this->syncVariants($product, $request);
        $this->storeImages($product, $request);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load('images', 'variants');

        return view('admin.products.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);
        unset($data['spec_labels'], $data['spec_values']);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name'], $product->id);
        $this->applyFlags($request, $data, $product);
        $data['specifications'] = $this->normalizeSpecifications($request);

        $product->update($data);
        $this->syncVariants($product, $request);
        $this->storeImages($product, $request);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            $this->deletePublicUpload($image->path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless((int) $image->product_id === (int) $product->id, 404);
        $wasPrimary = $image->is_primary;
        $this->deletePublicUpload($image->path);
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('position')->first()?->update(['is_primary' => true]);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Image removed.']);
        }

        return back()->with('status', 'Image removed.');
    }

    /* ------------------------------------------------------------------ */
    private function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id'      => ['required', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:180'],
            'slug'             => ['nullable', 'string', 'max:180'],
            'sku'              => ['nullable', 'string', 'max:60'],
            'brand'            => ['nullable', 'string', 'max:80'],
            'short_description'=> ['nullable', 'string', 'max:500'],
            'description'      => ['nullable', 'string'],
            'regular_price'    => ['required', 'numeric', 'min:0'],
            'sale_price'       => ['nullable', 'numeric', 'min:0'],
            'stock_quantity'   => ['required', 'integer', 'min:0'],
            'unit'             => ['nullable', 'string', 'max:40'],
            'flash_sale_progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'meta_title'       => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'spec_labels'      => ['nullable', 'array'],
            'spec_labels.*'    => ['nullable', 'string', 'max:120'],
            'spec_values'      => ['nullable', 'array'],
            'spec_values.*'    => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function normalizeSpecifications(Request $request): array
    {
        $labels = $request->input('spec_labels', []);
        $values = $request->input('spec_values', []);
        $rows = [];

        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            $value = trim((string) ($values[$i] ?? ''));
            if ($label === '' && $value === '') {
                continue;
            }
            $rows[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $rows;
    }

    private function applyFlags(Request $request, array &$data, ?Product $product = null): void
    {
        $data['is_published']   = $request->boolean('is_published');
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_flash_sale']  = $request->boolean('is_flash_sale');

        if ($data['is_flash_sale']) {
            $wasFlash = $product?->is_flash_sale ?? false;
            if (! $wasFlash) {
                $data['flash_sale_position'] = (int) Product::where('is_flash_sale', true)->max('flash_sale_position') + 1;
            }
            if ($request->filled('flash_sale_progress')) {
                $data['flash_sale_progress'] = (int) $request->input('flash_sale_progress');
            } elseif (! $wasFlash) {
                $data['flash_sale_progress'] = 50;
            }
        } else {
            $data['flash_sale_position'] = 0;
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function syncVariants(Product $product, Request $request): void
    {
        $sizes  = collect(explode(',', (string) $request->input('sizes')))->map(fn ($s) => trim($s))->filter()->values();
        $colors = collect(explode(',', (string) $request->input('colors')))->map(fn ($s) => trim($s))->filter()->values();

        // Only replace variants if the admin actually supplied values in either box.
        if ($request->has('sizes') || $request->has('colors')) {
            $product->variants()->delete();
            $pos = 0;
            foreach ($sizes as $s) {
                ProductVariant::create(['product_id' => $product->id, 'type' => 'Size', 'value' => $s, 'position' => $pos++]);
            }
            foreach ($colors as $c) {
                ProductVariant::create(['product_id' => $product->id, 'type' => 'Color', 'value' => $c, 'position' => $pos++]);
            }
        }

        // Process SKU Combination Matrix
        if ($request->has('sku_matrix_submitted') || $request->has('sku_matrix')) {
            $submittedSkuIds = [];
            $matrixInput = $request->input('sku_matrix', []);

            if (is_array($matrixInput)) {
                foreach ($matrixInput as $item) {
                    $attributes = isset($item['attributes']) && is_array($item['attributes']) ? array_filter($item['attributes']) : [];
                    if (empty($attributes)) {
                        continue;
                    }

                    $existingId = ! empty($item['id']) ? (int) $item['id'] : null;

                    $skuCode  = ! empty($item['sku']) ? trim((string) $item['sku']) : null;
                    if (empty($skuCode)) {
                        $skuCode = $this->generateAutoSku($product, $attributes, $existingId);
                    }

                    $stock    = max(0, (int) ($item['stock'] ?? 0));
                    $priceAdj = (float) ($item['price_adjustment'] ?? 0);
                    $isActive = isset($item['is_active']) ? (bool) $item['is_active'] : true;

                    $skuRecord = $existingId ? ProductSku::where('product_id', $product->id)->find($existingId) : null;

                    if ($skuRecord) {
                        $skuRecord->update([
                            'sku'              => $skuCode,
                            'attributes'       => $attributes,
                            'price_adjustment' => $priceAdj,
                            'stock_quantity'   => $stock,
                            'is_active'        => $isActive,
                        ]);
                        $submittedSkuIds[] = $skuRecord->id;
                    } else {
                        $newSku = ProductSku::create([
                            'product_id'       => $product->id,
                            'sku'              => $skuCode,
                            'attributes'       => $attributes,
                            'price_adjustment' => $priceAdj,
                            'stock_quantity'   => $stock,
                            'is_active'        => $isActive,
                        ]);
                        $submittedSkuIds[] = $newSku->id;
                    }
                }
            }

            // Clean up non-submitted matrix rows for this product
            ProductSku::where('product_id', $product->id)->whereNotIn('id', $submittedSkuIds)->delete();
        }

        if ($product->skus()->exists()) {
            $product->syncTotalStock();
        } else {
            // No variant SKUs exist: preserve the main form stock_quantity
            if ($request->has('stock_quantity')) {
                $product->update(['stock_quantity' => max(0, (int) $request->input('stock_quantity'))]);
            }
        }
    }

    private function generateAutoSku(Product $product, array $attributes, ?int $ignoreSkuId = null): string
    {
        $product->loadMissing('category');

        // Product SKU prefix: product->sku if present, otherwise category 3 letters
        if (! empty($product->sku)) {
            $prefix = trim((string) $product->sku);
        } else {
            $categoryName = $product->category?->name ?: ($product->name ?: 'PRD');
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $categoryName), 0, 3)) ?: 'PRD';
        }

        $sizeVal = '';
        $colorVal = '';
        $otherVals = [];

        foreach ($attributes as $k => $v) {
            $kLower = strtolower((string)$k);
            $vStr = (string)$v;
            if (str_contains($kLower, 'size')) {
                $sizeVal = preg_replace('/[^A-Za-z0-9]/', '', $vStr);
            } elseif (str_contains($kLower, 'col')) {
                // Color first word (e.g. "Dark Brown" -> "Dark", "Black" -> "Black")
                $words = explode(' ', trim($vStr));
                $colorVal = preg_replace('/[^A-Za-z0-9]/', '', $words[0]);
            } else {
                $cleanOther = preg_replace('/[^A-Za-z0-9]/', '', $vStr);
                if ($cleanOther !== '') {
                    $otherVals[] = $cleanOther;
                }
            }
        }

        $part = '';
        if ($colorVal !== '') {
            $part .= $colorVal;
        }
        if ($sizeVal !== '') {
            $part .= $sizeVal;
        }
        if (! empty($otherVals)) {
            $part .= implode('', $otherVals);
        }
        if ($part === '') {
            $part = 'VAR';
        }

        $baseSku = "{$prefix}-{$part}";
        $candidate = $baseSku;
        $counter = 1;

        while (ProductSku::where('sku', $candidate)->when($ignoreSkuId, fn ($q) => $q->where('id', '!=', $ignoreSkuId))->exists()) {
            $candidate = "{$baseSku}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    private function storeImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $request->validate(['images.*' => ['image', 'max:4096']]);

        $dir = public_path('uploads/products');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $position = (int) $product->images()->max('position');

        foreach ($request->file('images') as $file) {
            $filename = Str::uuid()->toString() . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dir, $filename);
            $path = 'uploads/products/' . $filename;

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'alt'        => $product->name,
                'is_primary' => ! $hasPrimary,
                'position'   => ++$position,
            ]);
            $hasPrimary = true;
        }
    }

    private function deletePublicUpload(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $relative = ltrim($path, '/');

        $candidates = array_unique([
            public_path($relative),
            base_path('public/' . $relative),
            storage_path('app/public/' . preg_replace('#^(storage/|uploads/)#', '', $relative)),
            storage_path('app/public/' . $relative),
        ]);

        foreach ($candidates as $full) {
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
