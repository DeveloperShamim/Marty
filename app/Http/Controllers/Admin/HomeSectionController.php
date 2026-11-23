<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\HomeSections;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Homepage → Sections: which homepage sections show, and in what order. */
class HomeSectionController extends Controller
{
    /** Where the pencil on each built-in section goes: the page that fills it */
    private const EDITORS = [
        'trust'         => ['admin.features.index', 'Trust strip'],
        'categories'    => ['admin.categories.index', 'Categories'],
        'deals'         => ['admin.flash-sale.index', 'Flash sale'],
        'curated'       => ['admin.products.index', 'Products'],
        'brands'        => ['admin.brands.index', 'Brands'],
        'category_rows' => ['admin.categories.index', 'Categories'],
        'reviews'       => ['admin.reviews.index', 'Reviews'],
        'just_for_you'  => ['admin.products.index', 'Products'],
        'coupons'       => ['admin.coupons.index', 'Coupons'],
    ];

    public function index()
    {
        $catalog = HomeSections::catalog();
        $user = auth()->user();

        return view('admin.home-sections.index', [
            'sections' => collect(HomeSections::all())->map(function ($row) use ($catalog, $user) {
                $blockId = \App\Models\HomeBlock::idFromKey($row['key']);
                [$route, $page] = self::EDITORS[$row['key']] ?? [null, null];
                $area = $route ? explode('.', $route)[1] : null;

                return $row + [
                    'name'  => $catalog[$row['key']][0],
                    'hint'  => $catalog[$row['key']][1],
                    'fixed' => in_array($row['key'], HomeSections::FIXED, true),
                    'block' => $blockId,
                    // Pencil: a section of the owner's own opens its editor; a built-in one opens the page that fills it
                    'edit'  => $blockId ? route('admin.home-blocks.edit', $blockId)
                        : ($route && \App\Support\StaffAccess::allows($user, $area) ? route($route) : null),
                    'editLabel' => $blockId ? 'Edit section' : ($page ? 'Edit in ' . $page : null),
                ];
            }),
            'types' => \App\Models\HomeBlock::TYPES,
        ]);
    }

    public function update(Request $request)
    {
        $keys = array_keys(HomeSections::catalog());

        if ($request->boolean('reset')) {
            HomeSections::save($keys, $keys);
            Setting::forgetCache();
            ActivityLogger::log('Homepage Sections Reset', 'Showed every homepage section in the default order.');

            return back()->with('status', 'Homepage sections reset: all on, default order.');
        }

        $data = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['string', Rule::in($keys)],
            'on'      => ['nullable', 'array'],
            'on.*'    => ['string', Rule::in($keys)],
        ]);

        HomeSections::save($data['order'], $data['on'] ?? []);
        Setting::forgetCache();
        ActivityLogger::log('Homepage Sections Updated', 'Changed which homepage sections show and their order.');

        return back()->with('status', 'Homepage sections saved.');
    }
}
