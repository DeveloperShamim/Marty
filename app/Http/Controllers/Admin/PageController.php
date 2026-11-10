<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Store content → Pages: About us, FAQ, Shipping, Returns and any other simple page. */
class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('position')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['is_published' => true, 'show_in_footer' => true, 'position' => Page::max('position') + 1])]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));
        ActivityLogger::log('Page Created', "Created the page \"{$page->title}\".");

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" created.");
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page));
        ActivityLogger::log('Page Updated', "Updated the page \"{$page->title}\".");

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" saved.");
    }

    public function destroy(Page $page)
    {
        $page->delete();
        ActivityLogger::log('Page Deleted', "Deleted the page \"{$page->title}\".");

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" deleted.");
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('title')))]);

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:120'],
            'slug'             => ['required', 'string', 'max:120', Rule::notIn(Page::RESERVED), Rule::unique('pages', 'slug')->ignore($page?->id)],
            'body'             => ['nullable', 'string', 'max:30000'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'position'         => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'slug.not_in' => 'That address is already used by the store. Pick another one.',
        ]);

        return $data + [
            'is_published'   => $request->boolean('is_published'),
            'show_in_footer' => $request->boolean('show_in_footer'),
            'position'       => (int) ($data['position'] ?? 0),
        ];
    }
}
