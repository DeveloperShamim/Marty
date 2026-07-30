<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.brands.index', [
            'brands' => Brand::withCount('products')->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.brands.form', ['brand' => new Brand(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $this->handleImages($request, $data);

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('status', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $this->validateData($request, $brand);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name'], $brand->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $this->handleImages($request, $data, $brand);

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('status', 'Brand updated successfully.');
    }

    public function toggleFeatured(Brand $brand)
    {
        $brand->update([
            'is_featured' => ! $brand->is_featured,
        ]);

        $statusMessage = $brand->is_featured
            ? "Brand '{$brand->name}' set as featured."
            : "Brand '{$brand->name}' un-featured.";

        return back()->with('status', $statusMessage);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->logo && ! str_starts_with($brand->logo, 'http')) {
            Storage::disk('public')->delete($brand->logo);
        }
        if ($brand->banner && ! str_starts_with($brand->banner, 'http')) {
            Storage::disk('public')->delete($brand->banner);
        }
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Brand deleted.');
    }

    private function validateData(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:120'],
            'slug'             => ['nullable', 'string', 'max:120'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'website'          => ['nullable', 'string', 'max:255'],
            'position'         => ['nullable', 'integer', 'min:0'],
            'meta_title'       => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'logo_file'        => ['nullable', 'image', 'max:4096'],
            'banner_file'      => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function handleImages(Request $request, array &$data, ?Brand $brand = null): void
    {
        unset($data['logo_file'], $data['banner_file']);

        if ($request->hasFile('logo_file')) {
            if ($brand && $brand->logo && ! str_starts_with($brand->logo, 'http')) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo_file')->store('brands/logos', 'public');
        }

        if ($request->hasFile('banner_file')) {
            if ($brand && $brand->banner && ! str_starts_with($brand->banner, 'http')) {
                Storage::disk('public')->delete($brand->banner);
            }
            $data['banner'] = $request->file('banner_file')->store('brands/banners', 'public');
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'brand';
        $slug = $baseSlug;
        $counter = 1;

        while (Brand::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
