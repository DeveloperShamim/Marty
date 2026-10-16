<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\HeaderMenu;
use Illuminate\Http\Request;

/** Homepage → Menu bar: what the store's top menu shows, in which order, and the dropdown under each item. */
class HeaderMenuController extends Controller
{
    public function index()
    {
        return view('admin.header-menu.index', [
            'items'      => HeaderMenu::saved(),
            'isCustom'   => HeaderMenu::isCustom(),
            'types'      => collect(HeaderMenu::TYPES)->map(fn ($t, $key) => ['key' => $key, 'label' => $t[0], 'ref' => $t[1], 'child' => $t[2]])->values(),
            'categories' => Category::with('parent:id,name')->get(['id', 'name', 'parent_id', 'is_active'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => ($c->parent ? $c->parent->name . ' › ' : '') . $c->name . ($c->is_active ? '' : ' (hidden)')])
                ->sortBy(fn ($c) => mb_strtolower($c['name']))->values(),
            'brands'     => Brand::orderBy('name')->get(['id', 'name', 'is_active'])
                ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name . ($b->is_active ? '' : ' (hidden)')]),
        ]);
    }

    public function update(Request $request)
    {
        if ($request->boolean('reset')) {
            Setting::put('header_menu', '');
            Setting::forgetCache();
            ActivityLogger::log('Menu Bar Reset', 'Reset the store menu bar to the default.');

            return back()->with('status', 'Menu bar reset to the default: Home, All Products, all categories and Brands.');
        }

        $request->validate(['menu' => ['required', 'json', 'max:20000']]);
        $items = HeaderMenu::sanitize((array) json_decode($request->input('menu'), true));
        if (! $items) {
            return back()->withErrors(['menu' => 'Add at least one menu item.']);
        }

        Setting::put('header_menu', json_encode($items));
        Setting::forgetCache();
        ActivityLogger::log('Menu Bar Updated', 'Updated the store menu bar (' . count($items) . ' items).');

        return back()->with('status', 'Menu bar saved. It is live on the store now.');
    }
}
