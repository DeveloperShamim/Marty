<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        // Main categories in order, each followed by its sub-categories
        $all = Category::withCount('products')->with('parent:id,name')->orderBy('position')->orderBy('name')->get();
        $children = $all->whereNotNull('parent_id')->groupBy('parent_id');
        $ordered = $all->whereNull('parent_id')
            ->flatMap(fn ($cat) => collect([$cat])->concat($children[$cat->id] ?? []))
            ->concat($all->whereNotNull('parent_id')->reject(fn ($c) => $all->contains('id', $c->parent_id)))
            ->values();

        return view('admin.categories.index', ['categories' => $ordered]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'parent_id' => request()->integer('parent') ?: null]),
            'parents'  => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $this->handleImage($request, $data);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents'  => $this->parentOptions($category),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request, $category);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name'], $category->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $this->handleImage($request, $data);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function toggleFeatured(Category $category)
    {
        $category->update([
            'is_featured' => ! $category->is_featured,
        ]);

        $statusMessage = $category->is_featured
            ? "Category '{$category->name}' featured on homepage."
            : "Category '{$category->name}' un-featured from homepage.";

        return back()->with('status', $statusMessage);
    }

    public function destroy(Category $category)
    {
        if ($category->image && ! str_starts_with($category->image, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    /** Main categories a category can sit under (one level only, never itself). */
    private function parentOptions(?Category $category = null)
    {
        return Category::topLevel()
            ->when($category?->exists, fn ($q) => $q->where('id', '!=', $category->id))
            ->orderBy('position')->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'parent_id'        => ['nullable', 'integer', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:120'],
            'slug'             => ['nullable', 'string', 'max:120'],
            'icon'             => ['nullable', 'string', 'max:16'],
            'description'      => ['nullable', 'string', 'max:500'],
            'position'         => ['nullable', 'integer', 'min:0'],
            'meta_title'       => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'image_file'       => ['nullable', 'image', 'max:4096'],
        ]);

        // One level only: the parent must be a main category, and a category with sub-categories stays a main one.
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = Category::find($parentId);
            $error = match (true) {
                $category && (int) $parentId === $category->id => 'A category cannot be inside itself.',
                $parent->parent_id !== null => "{$parent->name} is already a sub-category. Choose a main category.",
                $category && $category->children()->exists() => 'This category has its own sub-categories, so it must stay a main category.',
                default => null,
            };
            if ($error) {
                throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => $error]);
            }
        }
        $data['parent_id'] = $parentId ?: null;

        return $data;
    }

    private function handleImage(Request $request, array &$data): void
    {
        unset($data['image_file']);
        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('categories', 'public');
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
