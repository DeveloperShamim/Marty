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
    public function index()
    {
        return view('admin.home-sections.index', [
            'sections' => collect(HomeSections::all())->map(fn ($row) => $row + [
                'name' => HomeSections::SECTIONS[$row['key']][0],
                'hint' => HomeSections::SECTIONS[$row['key']][1],
                'fixed' => in_array($row['key'], HomeSections::FIXED, true),
            ]),
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
}
