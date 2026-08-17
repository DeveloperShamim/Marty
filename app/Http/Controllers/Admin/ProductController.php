<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
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
        $query = Product::with('category', 'brand', 'images', 'skus')->latest();

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhereHas('skus', function ($sq) use ($term) {
                      $sq->where('sku', 'like', "%{$term}%")
                         ->orWhere('attributes', 'like', "%{$term}%");
                  });
            });
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

    public function export(Request $request)
    {
        $query = Product::with('category', 'brand', 'images', 'skus')->latest();

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhereHas('skus', function ($sq) use ($term) {
                      $sq->where('sku', 'like', "%{$term}%")
                         ->orWhere('attributes', 'like', "%{$term}%");
                  });
            });
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $products = $query->get();
        $fileName = 'products-export-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            'id', 'name', 'sku', 'category', 'brand',
            'regular_price', 'sale_price', 'stock_quantity',
            'is_published', 'is_featured', 'is_new_arrival', 'is_best_seller',
            'short_description', 'description', 'image_urls'
        ];

        $callback = function () use ($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $p) {
                $imageUrls = $p->images->map(fn($img) => $img->url())->implode(', ');

                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->sku,
                    $p->category?->name ?? '',
                    $p->brand?->name ?? '',
                    $p->regular_price,
                    $p->sale_price,
                    $p->stock_quantity,
                    $p->is_published ? '1' : '0',
                    $p->is_featured ? '1' : '0',
                    $p->is_new_arrival ? '1' : '0',
                    $p->is_best_seller ? '1' : '0',
                    $p->short_description,
                    $p->description,
                    $imageUrls,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sampleCsv()
    {
        $fileName = 'sample-products-import.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $columns = [
            'name', 'sku', 'category', 'brand',
            'regular_price', 'sale_price', 'stock_quantity',
            'is_published', 'is_featured',
            'short_description', 'description', 'image_urls'
        ];

        $sampleData = [
            [
                'Premium Leather Sneakers', 'SNK-BLK-42', 'Footwear', 'Nike',
                '5500.00', '4990.00', '25', '1', '1',
                'Genuine leather sneakers with comfortable sole.',
                'Designed for daily wear with high-grade leather upper and memory foam insole.',
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600'
            ],
            [
                'Classic Quartz Watch', 'WCH-GLD-01', 'Watches', 'Casio',
                '3200.00', '2850.00', '15', '1', '0',
                'Water resistant quartz watch with stainless steel strap.',
                'Elegant watch featuring Japanese movement, mineral glass lens, and 30m water resistance.',
                'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'
            ],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('csv_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            return redirect()->back()->with('error', 'Please upload a valid CSV file (.csv format).');
        }

        $path = $file->getRealPath();
        $content = file_get_contents($path);

        if (empty(trim($content))) {
            return redirect()->back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Strip UTF-8 BOM if present
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = array_filter(explode("\n", $content), fn($line) => trim($line) !== '');

        if (empty($lines)) {
            return redirect()->back()->with('error', 'No valid rows found in the CSV file.');
        }

        // Auto-detect delimiter (, or ; or \t)
        $firstLine = reset($lines);
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        // Parse header
        $tempFile = fopen('php://memory', 'r+');
        fwrite($tempFile, implode("\n", $lines));
        rewind($tempFile);

        $headerRow = fgetcsv($tempFile, 8192, $delimiter);
        if (! $headerRow) {
            fclose($tempFile);
            return redirect()->back()->with('error', 'Unable to parse CSV headers.');
        }

        // Normalize headers
        $cleanHeaders = array_map(function ($h) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $h)));
        }, $headerRow);

        $headerMap = array_flip($cleanHeaders);

        // Alias helper
        $getVal = function ($row, array $keys) use ($headerMap) {
            foreach ($keys as $k) {
                if (isset($headerMap[$k])) {
                    $v = trim((string) ($row[$headerMap[$k]] ?? ''));
                    if ($v !== '') {
                        return $v;
                    }
                }
            }
            return null;
        };

        $created = 0;
        $updated = 0;
        $errors  = [];
        $lineNum = 1;

        while (($row = fgetcsv($tempFile, 8192, $delimiter)) !== false) {
            $lineNum++;
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                $id           = $getVal($row, ['id', 'product_id']);
                $name         = $getVal($row, ['name', 'title', 'product_name', 'product_title']);
                $sku          = $getVal($row, ['sku', 'product_sku', 'code', 'item_code']);
                $categoryName = $getVal($row, ['category', 'category_name', 'categories']);
                $brandName    = $getVal($row, ['brand', 'brand_name', 'manufacturer']);
                $regPriceVal  = $getVal($row, ['regular_price', 'price', 'unit_price', 'msrp']);
                $salePriceVal = $getVal($row, ['sale_price', 'discount_price', 'offer_price', 'special_price']);
                $stockVal     = $getVal($row, ['stock_quantity', 'stock', 'qty', 'quantity']);
                $pubVal       = $getVal($row, ['is_published', 'status', 'published', 'active']);
                $featVal      = $getVal($row, ['is_featured', 'featured']);
                $shortDesc    = $getVal($row, ['short_description', 'summary', 'excerpt']);
                $desc         = $getVal($row, ['description', 'detail', 'details', 'body']);
                $imageUrls    = $getVal($row, ['image_urls', 'images', 'image', 'photo', 'photos', 'picture']);

                if (empty($name)) {
                    $errors[] = "Line {$lineNum}: Skipped row missing product name.";
                    continue;
                }

                $regPrice = (float) (preg_replace('/[^0-9.]/', '', (string) $regPriceVal) ?: 0);
                $salePrice = (float) (preg_replace('/[^0-9.]/', '', (string) $salePriceVal) ?: 0);
                if ($salePrice <= 0) {
                    $salePrice = $regPrice;
                }
                $stock = (int) (preg_replace('/[^0-9]/', '', (string) $stockVal) ?: 0);
                $isPublished = $pubVal === null ? true : in_array(strtolower((string) $pubVal), ['1', 'true', 'yes', 'published', 'active'], true);
                $isFeatured = in_array(strtolower((string) $featVal), ['1', 'true', 'yes', 'featured'], true);

                // Category Resolution
                $categoryId = null;
                if (! empty($categoryName)) {
                    $cat = Category::firstOrCreate(
                        ['name' => $categoryName],
                        ['slug' => Str::slug($categoryName)]
                    );
                    $categoryId = $cat->id;
                } else {
                    $defaultCat = Category::firstOrCreate(
                        ['name' => 'General'],
                        ['slug' => 'general']
                    );
                    $categoryId = $defaultCat->id;
                }

                // Brand Resolution
                $brandId = null;
                if (! empty($brandName)) {
                    $b = Brand::firstOrCreate(
                        ['name' => $brandName],
                        ['slug' => Str::slug($brandName)]
                    );
                    $brandId = $b->id;
                }

                // Locate or initialize product
                $product = null;
                if (! empty($id)) {
                    $product = Product::find($id);
                }
                if (! $product && ! empty($sku)) {
                    $product = Product::where('sku', $sku)->first();
                }

                $isNew = false;
                if (! $product) {
                    $product = new Product();
                    $isNew = true;
                }

                $product->name              = $name;
                $product->slug              = Str::slug($name) . ($isNew ? '-' . Str::random(4) : '');
                $product->sku               = ! empty($sku) ? $sku : ('PRD-' . strtoupper(Str::random(6)));
                $product->category_id       = $categoryId;
                $product->brand_id          = $brandId;
                $product->regular_price     = $regPrice;
                $product->sale_price        = $salePrice > 0 ? $salePrice : $regPrice;
                $product->stock_quantity    = $stock;
                $product->is_published      = $isPublished;
                $product->is_featured       = $isFeatured;
                $product->short_description = $shortDesc;
                $product->description       = $desc;
                $product->save();

                // Process image URLs if provided
                if (! empty($imageUrls)) {
                    $urls = array_filter(array_map('trim', explode(',', $imageUrls)));
                    if (! empty($urls)) {
                        $pos = (int) $product->images()->max('position');
                        foreach ($urls as $uIdx => $u) {
                            $hasPrimary = $product->images()->where('is_primary', true)->exists();
                            ProductImage::create([
                                'product_id' => $product->id,
                                'path'       => $u,
                                'alt'        => $product->name,
                                'is_primary' => ! $hasPrimary && $uIdx === 0,
                                'position'   => ++$pos,
                            ]);
                        }
                    }
                }

                if ($isNew) {
                    $created++;
                } else {
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Line {$lineNum}: " . $e->getMessage();
            }
        }

        fclose($tempFile);

        $status = "Import successful! Created: {$created}, Updated: {$updated}.";
        if (count($errors) > 0) {
            $status .= " (" . count($errors) . " rows skipped or had errors: " . implode(' | ', array_slice($errors, 0, 3)) . ")";
        }

        return redirect()->route('admin.products.index')->with('status', $status);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
            $request->merge(['ids' => $ids]);
        }

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:products,id'],
        ]);

        $products = Product::with('images')->whereIn('id', $request->input('ids'))->get();

        $count = 0;
        foreach ($products as $p) {
            foreach ($p->images as $img) {
                $this->deletePublicUpload($img->path);
            }
            $p->delete();
            $count++;
        }

        return redirect()->route('admin.products.index')->with('status', "Successfully deleted {$count} " . Str::plural('product', $count) . '.');
    }

    public function create()
    {
        return view('admin.products.form', [
            'product'        => new Product(['is_published' => true]),
            'categories'     => Category::orderBy('name')->get(),
            'brands'         => Brand::where('is_active', true)->orderBy('name')->get(),
            'attributeTypes' => \App\Models\ProductAttributeType::with('values')->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        unset($data['spec_labels'], $data['spec_values']);
        if (!empty($data['brand_id'])) {
            $data['brand'] = Brand::find($data['brand_id'])?->name;
        }
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name']);
        if (empty($data['sku'])) {
            $data['sku'] = 'PRD-' . strtoupper(Str::random(8));
        }
        $this->applyFlags($request, $data, null);
        $data['specifications'] = $this->normalizeSpecifications($request);

        $product = Product::create($data);
        $this->syncVariants($product, $request);
        $this->storeImages($product, $request);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('images', 'variants');

        return view('admin.products.form', [
            'product'        => $product,
            'categories'     => Category::orderBy('name')->get(),
            'brands'         => Brand::where('is_active', true)->orderBy('name')->get(),
            'attributeTypes' => \App\Models\ProductAttributeType::with('values')->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);
        unset($data['spec_labels'], $data['spec_values']);
        if (!empty($data['brand_id'])) {
            $data['brand'] = Brand::find($data['brand_id'])?->name;
        } else if ($request->has('brand_id')) {
            $data['brand'] = null;
        }
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name'], $product->id);
        $this->applyFlags($request, $data, $product);
        $data['specifications'] = $this->normalizeSpecifications($request);

        $product->update($data);
        $this->syncVariants($product, $request);
        $this->storeImages($product, $request);
        $this->updateImageColors($product, $request);

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
            'brand_id'         => ['nullable', 'exists:brands,id'],
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
                $type = preg_match('/\d+\s*(g|kg|l|ml|oz|lb|liter|litre|gm|gram)/i', $s) ? 'Weight' : 'Size';
                ProductVariant::create(['product_id' => $product->id, 'type' => $type, 'value' => $s, 'position' => $pos++]);
            }
            foreach ($colors as $c) {
                $type = preg_match('/glass|plastic|plastick|bottle|jar|can|pack|bag|box|container/i', $c) ? 'Packaging' : 'Color';
                ProductVariant::create(['product_id' => $product->id, 'type' => $type, 'value' => $c, 'position' => $pos++]);
            }
        }

        $baseReg = (float) $product->regular_price;
        $baseSale = (float) ($product->sale_price ?? $product->regular_price);

        // Process SKU Combination Matrix
        if ($request->has('sku_matrix_submitted') || $request->has('sku_matrix')) {
            $submittedSkuIds = [];
            $matrixInput = $request->input('sku_matrix', []);
            $matrixAttributes = [];

            if (is_array($matrixInput)) {
                foreach ($matrixInput as $item) {
                    $attributes = isset($item['attributes']) && is_array($item['attributes']) ? array_filter($item['attributes']) : [];
                    if (empty($attributes)) {
                        continue;
                    }

                    foreach ($attributes as $type => $val) {
                        $tStr = trim((string) $type);
                        $vStr = trim((string) $val);
                        if ($tStr !== '' && $vStr !== '') {
                            $matrixAttributes[$tStr][$vStr] = true;
                        }
                    }

                    $existingId = ! empty($item['id']) ? (int) $item['id'] : null;

                    $skuCode  = ! empty($item['sku']) ? trim((string) $item['sku']) : null;
                    if (empty($skuCode)) {
                        $skuCode = $this->generateAutoSku($product, $attributes, $existingId);
                    }

                    $stock        = max(0, (int) ($item['stock'] ?? 0));
                    $regularPrice = (isset($item['regular_price']) && $item['regular_price'] !== '' && (float)$item['regular_price'] > 0) ? (float) $item['regular_price'] : ($baseReg > 0 ? $baseReg : null);
                    $salePrice    = (isset($item['sale_price']) && $item['sale_price'] !== '' && (float)$item['sale_price'] > 0) ? (float) $item['sale_price'] : ($baseSale > 0 ? $baseSale : null);
                    $priceAdj     = ($salePrice !== null && $baseSale > 0) ? max(0, $salePrice - $baseSale) : 0;
                    $isActive     = isset($item['is_active']) ? (bool) $item['is_active'] : true;

                    $skuRecord = $existingId ? ProductSku::where('product_id', $product->id)->find($existingId) : null;

                    if ($skuRecord) {
                        $skuRecord->update([
                            'sku'              => $skuCode,
                            'attributes'       => $attributes,
                            'price_adjustment' => $priceAdj,
                            'regular_price'    => $regularPrice,
                            'sale_price'       => $salePrice,
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
                            'regular_price'    => $regularPrice,
                            'sale_price'       => $salePrice,
                            'stock_quantity'   => $stock,
                            'is_active'        => $isActive,
                        ]);
                        $submittedSkuIds[] = $newSku->id;
                    }
                }
            }

            // Clean up non-submitted matrix rows for this product
            ProductSku::where('product_id', $product->id)->whereNotIn('id', $submittedSkuIds)->delete();

            // Rebuild product_variants table from matrix attributes
            if (! empty($matrixAttributes)) {
                $product->variants()->delete();
                $pos = 0;
                foreach ($matrixAttributes as $type => $valsMap) {
                    foreach (array_keys($valsMap) as $val) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'type'       => $type,
                            'value'      => $val,
                            'position'   => $pos++,
                        ]);
                    }
                }
            }
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
            if (str_contains($kLower, 'size') || str_contains($kLower, 'weight') || str_contains($kLower, 'vol') || str_contains($kLower, 'unit')) {
                $sizeVal = preg_replace('/[^A-Za-z0-9]/', '', $vStr);
            } elseif (str_contains($kLower, 'col') || str_contains($kLower, 'pack') || str_contains($kLower, 'type') || str_contains($kLower, 'flav')) {
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

        $newColors = $request->input('new_image_colors', []);
        $variantOptions = $product->variants()->pluck('value')->toArray();

        foreach ($request->file('images') as $i => $file) {
            $path = $this->saveUploadedProductImage($file, $dir);
            $colorVal = ! empty($newColors[$i]) ? trim((string) $newColors[$i]) : ($variantOptions[$i] ?? null);

            $seoAlt = $this->generateSeoAltText($product, $colorVal, $position + 1);

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'alt'        => $seoAlt,
                'color'      => $colorVal,
                'is_primary' => ! $hasPrimary,
                'position'   => ++$position,
            ]);
            $hasPrimary = true;
        }
    }

    private function generateSeoAltText(Product $product, ?string $colorTag, int $pos): string
    {
        $parts = [];
        $parts[] = $product->name;
        if ($colorTag) {
            $parts[] = "({$colorTag})";
        }
        if ($product->brand) {
            $parts[] = "by {$product->brand}";
        }
        $parts[] = "100% Pure & Organic";
        $parts[] = "ShodeshiFood BD";

        return implode(" — ", $parts);
    }

    private function saveUploadedProductImage($file, string $dir): string
    {
        $uuid = Str::uuid()->toString();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $uuid . '.' . $ext;
        $file->move($dir, $filename);

        return 'uploads/products/' . $filename;
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

    private function updateImageColors(Product $product, Request $request): void
    {
        if ($request->has('image_colors') && is_array($request->input('image_colors'))) {
            foreach ($request->input('image_colors') as $imgId => $colorVal) {
                $colorTag = trim((string) $colorVal) ?: null;
                $img = ProductImage::where('id', $imgId)->where('product_id', $product->id)->first();
                if ($img) {
                    $seoAlt = $this->generateSeoAltText($product, $colorTag, $img->position);
                    $img->update([
                        'color' => $colorTag,
                        'alt'   => $seoAlt,
                    ]);
                }
            }
        }

        if ($request->has('image_positions') && is_array($request->input('image_positions'))) {
            $positions = $request->input('image_positions');
            asort($positions);
            $firstImgId = array_key_first($positions);

            foreach ($positions as $imgId => $pos) {
                ProductImage::where('id', $imgId)->where('product_id', $product->id)->update([
                    'position'   => (int) $pos,
                    'is_primary' => ((int) $imgId === (int) $firstImgId),
                ]);
            }
        }
    }
}
