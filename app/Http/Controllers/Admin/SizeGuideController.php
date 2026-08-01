<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SizeGuideController extends Controller
{
    public function index()
    {
        $settings = [
            'size_guide_enabled'      => setting('size_guide_enabled', '1'),
            'size_guide_default_unit' => setting('size_guide_default_unit', 'cm'),
            'size_guide_custom_tip'   => setting('size_guide_custom_tip', ''),
        ];

        return view('admin.size-guide.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'size_guide_enabled'      => ['required', 'in:0,1'],
            'size_guide_default_unit' => ['required', 'in:cm,in'],
            'size_guide_custom_tip'   => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $val) {
            Setting::put($key, $val);
        }

        return redirect()->back()->with('status', 'Size Guide settings updated successfully.');
    }
}
