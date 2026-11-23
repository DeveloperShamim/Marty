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
    /** Titles shown above the Brands and Customer reviews sections. */
    private const TITLE_KEYS = ['home_featured_brands_title', 'home_featured_brands_subtitle', 'home_reviews_title', 'home_reviews_subtitle'];

    public function index()
    {
        return view('admin.home-sections.index', [
            'sections' => collect(HomeSections::all())->map(fn ($row) => $row + [
                'name' => HomeSections::SECTIONS[$row['key']][0],
                'hint' => HomeSections::SECTIONS[$row['key']][1],
                'fixed' => in_array($row['key'], HomeSections::FIXED, true),
            ]),
            'titles' => collect(self::TITLE_KEYS)->mapWithKeys(fn ($key) => [$key => (string) setting($key, '')])->all(),
        ]);
    }

    public function update(Request $request)
    {
        $keys = array_keys(HomeSections::SECTIONS);

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

    /** Section titles for the Brands and Customer reviews blocks. */
    public function updateTitles(Request $request)
    {
        $data = $request->validate([
            'home_featured_brands_title'    => ['nullable', 'string', 'max:80'],
            'home_featured_brands_subtitle' => ['nullable', 'string', 'max:200'],
            'home_reviews_title'            => ['nullable', 'string', 'max:80'],
            'home_reviews_subtitle'         => ['nullable', 'string', 'max:200'],
        ]);

        foreach (self::TITLE_KEYS as $key) {
            Setting::put($key, (string) ($data[$key] ?? ''));
        }
        Setting::forgetCache();
        ActivityLogger::log('Homepage Section Titles Updated', 'Changed the Brands and Customer reviews section titles.');

        return back()->with('status', 'Section titles saved.');
    }
}
