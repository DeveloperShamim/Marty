<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('placement')->orderBy('position')->orderBy('id')->get();

        return view('admin.banners.index', [
            'banners'    => $banners,
            'placements' => Banner::PLACEMENTS,
            'slider'     => Banner::slider(),
        ]);
    }

    public function create(Request $request)
    {
        $defaultPlacement = $request->query('placement', 'hero');
        if (! array_key_exists($defaultPlacement, Banner::PLACEMENTS)) {
            $defaultPlacement = 'hero';
        }

        return view('admin.banners.form', [
            'banner'     => new Banner(['is_active' => true, 'placement' => $defaultPlacement, 'style' => 'brand', 'position' => 0]),
            'placements' => Banner::PLACEMENTS,
            'styles'     => Banner::STYLES,
            'devices'    => Banner::DEVICES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $this->handleImage($request, $data);

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', [
            'banner'     => $banner,
            'placements' => Banner::PLACEMENTS,
            'styles'     => Banner::STYLES,
            'devices'    => Banner::DEVICES,
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $this->handleImage($request, $data, $banner);

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function sliderSettings(Request $request)
    {
        $data = $request->validate([
            'hero_delay'     => ['required', 'numeric', 'min:2', 'max:15'],
            'hero_video_url' => ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
                if (trim((string) $value) !== '' && ! Banner::heroVideo($value)) {
                    $fail('Paste a YouTube link, or a direct link to an .mp4 or .webm file.');
                }
            }],
        ]);

        \App\Models\Setting::put('hero_autoplay', $request->boolean('hero_autoplay') ? '1' : '0');
        \App\Models\Setting::put('hero_arrows', $request->boolean('hero_arrows') ? '1' : '0');
        \App\Models\Setting::put('hero_dots', $request->boolean('hero_dots') ? '1' : '0');
        \App\Models\Setting::put('hero_delay', (string) round((float) $data['hero_delay'], 1));
        \App\Models\Setting::put('hero_video_url', trim((string) ($data['hero_video_url'] ?? '')));
        \App\Models\Setting::forgetCache();
        \App\Services\ActivityLogger::log('Hero Slider Settings Updated', 'Changed how the homepage slider plays.');

        return back()->with('status', 'Slider settings saved.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('status', $banner->is_active ? 'Banner is switched on. It shows on the store inside its dates.' : 'Banner hidden from the storefront.');
    }

    public function destroy(Banner $banner)
    {
        foreach ([$banner->image, $banner->image_mobile] as $path) {
            if ($path && ! str_starts_with($path, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'subtitle'    => ['nullable', 'string', 'max:500'],
            'badge'       => ['nullable', 'string', 'max:60'],
            'link_url'    => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'placement'   => ['required', 'in:' . implode(',', array_keys(Banner::PLACEMENTS))],
            'style'       => ['nullable', 'string', 'max:30'],
            'position'    => ['nullable', 'integer', 'min:0'],
            'image_file'  => ['nullable', 'image', 'max:4096'],
            'image_url'   => ['nullable', 'string', 'max:255'],
            'image_mobile_file' => ['nullable', 'image', 'max:4096'],
            'show_on'     => ['nullable', 'in:' . implode(',', array_keys(Banner::DEVICES))],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after:starts_at'],
        ], [
            'ends_at.after' => 'The end must be after the start.',
        ]);

        // Promo cards can be kept to phones or desktops; carousel slides always show on both
        $data['show_on'] = $data['placement'] === 'hero_side' ? ($data['show_on'] ?? 'both') : 'both';
        $data['starts_at'] = $data['starts_at'] ?? null;
        $data['ends_at'] = $data['ends_at'] ?? null;

        if (empty($data['style']) || ! array_key_exists($data['style'], Banner::STYLES)) {
            $data['style'] = 'brand';
        }

        $data['position'] = (int) ($data['position'] ?? 0);

        return $data;
    }

    private function handleImage(Request $request, array &$data, ?Banner $banner = null): void
    {
        unset($data['image_file'], $data['image_url'], $data['image_mobile_file']);

        // The picture phones see instead (optional)
        if ($request->boolean('remove_image_mobile')) {
            $data['image_mobile'] = null;
        } elseif ($request->hasFile('image_mobile_file')) {
            $data['image_mobile'] = $request->file('image_mobile_file')->store('banners', 'public');
        }

        if ($request->boolean('remove_image')) {
            $data['image'] = null;
        } elseif ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('banners', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }
    }
}
