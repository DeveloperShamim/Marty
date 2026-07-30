<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminDesignController extends Controller
{
    public function index()
    {
        $heroBanners = Banner::where('placement', 'hero')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('admin.design.index', [
            'heroBanners'       => $heroBanners,
            'brandPrimaryColor' => setting('brand_primary_color', '#E8751B'),
            'brandSecondaryColor' => setting('brand_secondary_color', '#353535'),
        ]);
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'brand_primary_color'   => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
            'brand_secondary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
        ]);

        Setting::put('brand_primary_color', $request->input('brand_primary_color'));
        if ($request->has('brand_secondary_color')) {
            Setting::put('brand_secondary_color', $request->input('brand_secondary_color', '#353535'));
        }

        return back()->with('status', 'Homepage theme & brand colors updated successfully!');
    }

    public function extractLogoColor(Request $request)
    {
        $logoPath = setting('logo');
        if (! $logoPath) {
            return back()->withErrors(['logo' => 'No custom logo uploaded yet. Upload a logo to auto-detect website colors.']);
        }

        $fullPath = storage_path('app/public/' . $logoPath);
        $extractedColor = extract_dominant_color_from_image($fullPath);

        if (! $extractedColor) {
            return back()->withErrors(['logo' => 'Could not detect a vibrant accent color from current logo. Select a color manually below.']);
        }

        Setting::put('brand_primary_color', $extractedColor);

        return back()->with('status', "Website brand color automatically set to {$extractedColor} from your uploaded logo!");
    }
}
