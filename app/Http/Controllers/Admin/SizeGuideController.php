<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SizeGuideController extends Controller
{
    public static function defaultShoes(): array
    {
        return [
            ['eu' => '39', 'usM' => '6.5', 'usW' => '8.0', 'uk' => '5.5', 'cm' => 24.5],
            ['eu' => '40', 'usM' => '7.5', 'usW' => '9.0', 'uk' => '6.5', 'cm' => 25.0],
            ['eu' => '41', 'usM' => '8.0', 'usW' => '9.5', 'uk' => '7.0', 'cm' => 25.8],
            ['eu' => '42', 'usM' => '8.5', 'usW' => '10.0', 'uk' => '7.5', 'cm' => 26.5],
            ['eu' => '43', 'usM' => '9.5', 'usW' => '11.0', 'uk' => '8.5', 'cm' => 27.3],
            ['eu' => '44', 'usM' => '10.5', 'usW' => '12.0', 'uk' => '9.5', 'cm' => 28.0],
            ['eu' => '45', 'usM' => '11.5', 'usW' => '13.0', 'uk' => '10.5', 'cm' => 28.8],
            ['eu' => '46', 'usM' => '12.0', 'usW' => '13.5', 'uk' => '11.0', 'cm' => 29.5],
        ];
    }

    public static function defaultBelts(): array
    {
        return [
            ['size' => '32 (S)', 'waistCm' => 81, 'pants' => '28 - 30', 'strapLengthCm' => 95],
            ['size' => '34 (M)', 'waistCm' => 86, 'pants' => '30 - 32', 'strapLengthCm' => 100],
            ['size' => '36 (L)', 'waistCm' => 91, 'pants' => '32 - 34', 'strapLengthCm' => 105],
            ['size' => '38 (XL)', 'waistCm' => 97, 'pants' => '34 - 36', 'strapLengthCm' => 110],
            ['size' => '40 (XXL)', 'waistCm' => 102, 'pants' => '36 - 38', 'strapLengthCm' => 115],
        ];
    }

    public static function defaultWatches(): array
    {
        return [
            ['wristCm' => '14.0 - 16.0', 'caseSize' => '36mm - 38mm', 'look' => 'Classic / Minimalist', 'strap' => '18mm - 20mm'],
            ['wristCm' => '16.0 - 18.0', 'caseSize' => '40mm - 42mm', 'look' => 'Standard / Versatile', 'strap' => '20mm - 22mm'],
            ['wristCm' => '18.0 - 20.0+', 'caseSize' => '44mm - 46mm', 'look' => 'Bold / Oversized', 'strap' => '22mm - 24mm'],
        ];
    }

    public function index()
    {
        $settings = [
            'size_guide_enabled'      => setting('size_guide_enabled', '1'),
            'size_guide_default_unit' => setting('size_guide_default_unit', 'cm'),
            'size_guide_custom_tip'   => setting('size_guide_custom_tip', ''),
        ];

        $shoesData = json_decode((string) setting('size_guide_shoes_data'), true);
        if (!is_array($shoesData) || empty($shoesData)) {
            $shoesData = self::defaultShoes();
        }

        $beltsData = json_decode((string) setting('size_guide_belts_data'), true);
        if (!is_array($beltsData) || empty($beltsData)) {
            $beltsData = self::defaultBelts();
        }

        $watchesData = json_decode((string) setting('size_guide_watches_data'), true);
        if (!is_array($watchesData) || empty($watchesData)) {
            $watchesData = self::defaultWatches();
        }

        return view('admin.size-guide.index', compact('settings', 'shoesData', 'beltsData', 'watchesData'));
    }

    public function update(Request $request)
    {
        if ($request->boolean('reset_defaults')) {
            Setting::forget('size_guide_shoes_data');
            Setting::forget('size_guide_belts_data');
            Setting::forget('size_guide_watches_data');

            return redirect()->back()->with('status', 'Size charts reset to international standard defaults.');
        }

        $validated = $request->validate([
            'size_guide_enabled'      => ['sometimes', 'in:0,1'],
            'size_guide_default_unit' => ['sometimes', 'in:cm,in'],
            'size_guide_custom_tip'   => ['nullable', 'string', 'max:500'],
            'shoes_data'              => ['nullable', 'string'],
            'belts_data'              => ['nullable', 'string'],
            'watches_data'            => ['nullable', 'string'],
        ]);

        if (isset($validated['size_guide_enabled'])) {
            Setting::put('size_guide_enabled', $validated['size_guide_enabled']);
        }
        if (isset($validated['size_guide_default_unit'])) {
            Setting::put('size_guide_default_unit', $validated['size_guide_default_unit']);
        }
        if ($request->has('size_guide_custom_tip')) {
            Setting::put('size_guide_custom_tip', $validated['size_guide_custom_tip'] ?? '');
        }

        if (!empty($validated['shoes_data'])) {
            $shoes = json_decode($validated['shoes_data'], true);
            if (is_array($shoes)) {
                Setting::put('size_guide_shoes_data', json_encode(array_values($shoes)));
            }
        }

        if (!empty($validated['belts_data'])) {
            $belts = json_decode($validated['belts_data'], true);
            if (is_array($belts)) {
                Setting::put('size_guide_belts_data', json_encode(array_values($belts)));
            }
        }

        if (!empty($validated['watches_data'])) {
            $watches = json_decode($validated['watches_data'], true);
            if (is_array($watches)) {
                Setting::put('size_guide_watches_data', json_encode(array_values($watches)));
            }
        }

        return redirect()->back()->with('status', 'Size Guide settings and custom charts updated successfully.');
    }
}
