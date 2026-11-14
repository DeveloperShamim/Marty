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

        $config = [
            'show_reviews' => $request->boolean('show_reviews'),
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
                'pillars' => collect($data['material']['pillars'] ?? [])
                    ->map(fn ($p) => ['title' => $clean($p['title'] ?? ''), 'text' => $clean($p['text'] ?? '')])
                    ->filter(fn ($p) => $p['title'] !== '' || $p['text'] !== '')->values()->all(),
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
}
