<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\LaunchMode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Homepage → Launch mode: the small-catalogue homepage switch and its content. */
class LaunchModeController extends Controller
{
    public function index()
    {
        return view('admin.launch-mode.index', [
            'on' => LaunchMode::isOn(),
            'config' => LaunchMode::config(),
            'picked' => LaunchMode::products(),
            'allProducts' => Product::query()->orderBy('name')->get(['id', 'name', 'is_published', 'stock_quantity']),
        ]);
    }

    public function update(Request $request)
    {
        $text = ['nullable', 'string', 'max:300'];
        $long = ['nullable', 'string', 'max:600'];

        $data = $request->validate([
            'launch_mode'            => ['nullable', 'boolean'],
            'show_reviews'           => ['nullable', 'boolean'],
            'hero.badge'             => $text,
            'hero.title'             => $text,
            'hero.text'              => $long,
            'hero.button'            => ['nullable', 'string', 'max:40'],
            'collection.label'       => $text,
            'collection.heading'     => $text,
            'collection.intro'       => $long,
            'product_ids'            => ['nullable', 'array', 'max:' . LaunchMode::MAX_PRODUCTS],
            'product_ids.*'          => ['nullable', 'integer', Rule::exists('products', 'id')],
            'material.on'            => ['nullable', 'boolean'],
            'material.label'         => $text,
            'material.heading'       => $text,
            'material.pillars'       => ['nullable', 'array', 'max:3'],
            'material.pillars.*.title' => $text,
            'material.pillars.*.text'  => $long,
            'material_image'         => ['nullable', 'image', 'max:4096'],
            'hero_image'             => ['nullable', 'image', 'max:4096'],
            'hero_color_image'       => ['nullable', 'array', 'max:8'],
            'hero_color_image.*'     => ['nullable', 'image', 'max:4096'],
            'pillar_image'           => ['nullable', 'array', 'max:3'],
            'pillar_image.*'         => ['nullable', 'image', 'max:4096'],
            'compare.on'             => ['nullable', 'boolean'],
            'compare.label'          => $text,
            'compare.heading'        => $text,
            'compare.rows'           => ['nullable', 'array', 'max:' . LaunchMode::MAX_COMPARE_ROWS],
            'compare.rows.*.label'   => $text,
            'compare.rows.*.values'  => ['nullable', 'array'],
            'compare.rows.*.values.*' => $text,
            'trust.on'               => ['nullable', 'boolean'],
            'trust.items'            => ['nullable', 'array', 'max:3'],
            'trust.items.*.icon'     => ['nullable', Rule::in(['cash', 'bolt', 'shield', 'truck', 'gift', 'star'])],
            'trust.items.*.title'    => $text,
            'trust.items.*.text'     => $long,
        ]);

        $clean = fn ($v) => trim((string) $v);

        // Pictures for the material section: keep what was saved unless a new one is uploaded or "Remove" is ticked
        $saved = LaunchMode::config()['material'] ?? [];
        $picture = function (?string $old, string $fileKey, string $removeKey) use ($request) {
            if ($request->hasFile($fileKey)) {
                $this->deletePicture($old);

                return $request->file($fileKey)->store('launch', 'public');
            }
            if ($request->boolean($removeKey)) {
                $this->deletePicture($old);

                return null;
            }

            return $old;
        };
        $materialImage = $picture($saved['image'] ?? null, 'material_image', 'material_image_remove');

        // Hero pictures: one main picture, and one per colour of the first product (keyed by colour name)
        $savedHero = LaunchMode::config()['hero'] ?? [];
        $heroImage = $picture($savedHero['image'] ?? null, 'hero_image', 'hero_image_remove');
        $colorImages = [];
        $colorKeys = array_unique(array_merge(array_keys($savedHero['color_images'] ?? []), array_keys($request->file('hero_color_image', []) ?: []), array_keys((array) $request->input('hero_color_image_remove', []))));
        foreach ($colorKeys as $key) {
            $key = mb_strtolower(trim((string) $key));
            if ($key === '') {
                continue;
            }
            $path = $picture($savedHero['color_images'][$key] ?? null, 'hero_color_image.' . $key, 'hero_color_image_remove.' . $key);
            if ($path) {
                $colorImages[$key] = $path;
            }
        }
        $pillarImages = [];
        foreach (range(0, 2) as $i) {
            $pillarImages[$i] = $picture($saved['pillars'][$i]['image'] ?? null, "pillar_image.$i", "pillar_image_remove.$i");
        }

        $config = [
            'show_reviews' => $request->boolean('show_reviews'),
            'hero' => [
                'badge' => $clean($data['hero']['badge'] ?? ''),
                'title' => $clean($data['hero']['title'] ?? ''),
                'text' => $clean($data['hero']['text'] ?? ''),
                'button' => $clean($data['hero']['button'] ?? ''),
                'image' => $heroImage,
                'color_images' => $colorImages,
            ],
            'collection' => [
                'label' => $clean($data['collection']['label'] ?? ''),
                'heading' => $clean($data['collection']['heading'] ?? ''),
                'intro' => $clean($data['collection']['intro'] ?? ''),
            ],
            // Empty slots are dropped and a product picked twice counts once
            'product_ids' => collect($data['product_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'material' => [
                'on' => $request->boolean('material.on'),
                'label' => $clean($data['material']['label'] ?? ''),
                'heading' => $clean($data['material']['heading'] ?? ''),
                'image' => $materialImage,
                'pillars' => collect($data['material']['pillars'] ?? [])
                    ->map(fn ($p, $i) => ['title' => $clean($p['title'] ?? ''), 'text' => $clean($p['text'] ?? ''), 'image' => $pillarImages[$i] ?? null])
                    ->filter(fn ($p) => $p['title'] !== '' || $p['text'] !== '' || $p['image'])->values()->all(),
            ],
            'compare' => [
                'on' => $request->boolean('compare.on'),
                'label' => $clean($data['compare']['label'] ?? ''),
                'heading' => $clean($data['compare']['heading'] ?? ''),
                'rows' => collect($data['compare']['rows'] ?? [])
                    ->map(fn ($r) => [
                        'label' => $clean($r['label'] ?? ''),
                        'values' => collect($r['values'] ?? [])->map($clean)->filter(fn ($v) => $v !== '')->all(),
                    ])
                    ->filter(fn ($r) => $r['label'] !== '')->values()->all(),
            ],
            'trust' => [
                'on' => $request->boolean('trust.on'),
                'items' => collect($data['trust']['items'] ?? [])
                    ->map(fn ($t) => ['icon' => $t['icon'] ?? 'shield', 'title' => $clean($t['title'] ?? ''), 'text' => $clean($t['text'] ?? '')])
                    ->filter(fn ($t) => $t['title'] !== '')->values()->all(),
            ],
        ];

        $on = $request->boolean('launch_mode');
        $wasOn = LaunchMode::isOn();
        LaunchMode::save($config, $on);
        Setting::forgetCache();

        ActivityLogger::log('Launch Mode Updated', match (true) {
            $on && ! $wasOn => 'Switched launch mode on.',
            ! $on && $wasOn => 'Switched launch mode off; the normal homepage is back.',
            default => 'Changed the launch mode homepage content.',
        });

        return back()->with('status', $on ? 'Saved. Launch mode is on.' : 'Saved. Launch mode is off, so the normal homepage shows.');
    }

    /** Delete an uploaded launch picture from storage (pasted links and other folders are left alone). */
    private function deletePicture(?string $path): void
    {
        if ($path && str_starts_with($path, 'launch/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }
}
