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
        ]);
    }

    public function create()
    {
        return view('admin.banners.form', [
            'banner'     => new Banner(['is_active' => true, 'placement' => 'hero', 'style' => 'brand', 'position' => 0]),
            'placements' => Banner::PLACEMENTS,
            'styles'     => Banner::STYLES,
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

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('status', $banner->is_active ? 'Banner is now visible on the storefront.' : 'Banner hidden from the storefront.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && ! str_starts_with($banner->image, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'       => ['nullable', 'string', 'max:180'],
            'subtitle'    => ['nullable', 'string', 'max:400'],
            'badge'       => ['nullable', 'string', 'max:60'],
            'link_url'    => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'placement'   => ['required', 'in:' . implode(',', array_keys(Banner::PLACEMENTS))],
            'style'       => ['required', 'in:' . implode(',', array_keys(Banner::STYLES))],
            'position'    => ['nullable', 'integer', 'min:0'],
            'image_file'  => ['nullable', 'image', 'max:4096'],
            'image_url'   => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function handleImage(Request $request, array &$data, ?Banner $banner = null): void
    {
        unset($data['image_file'], $data['image_url']);

        if ($request->boolean('remove_image')) {
            $data['image'] = null;
        } elseif ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('banners', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }
    }
}
