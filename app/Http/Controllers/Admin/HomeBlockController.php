<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeBlock;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\HomeSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Homepage → Sections → Add section: product rows, promo bands, wide banners, card grids and stats rows. */
class HomeBlockController extends Controller
{
    public function create(Request $request)
    {
        $type = (string) $request->query('type', 'products');
        abort_unless(isset(HomeBlock::TYPES[$type]), 404);

        return $this->form(new HomeBlock(['type' => $type, 'data' => self::starter($type)]));
    }

    public function store(Request $request)
    {
        $type = (string) $request->input('type');
        abort_unless(isset(HomeBlock::TYPES[$type]), 422);

        $block = new HomeBlock(['type' => $type]);
        $this->fill($request, $block);
        $block->save();

        // New sections go to the end of the list, switched on
        $all = HomeSections::all();
        HomeSections::save(array_column($all, 'key'), array_column(array_filter($all, fn ($r) => $r['on']), 'key'));
        Setting::forgetCache();
        ActivityLogger::log('Homepage Section Added', $block->typeName() . ': ' . $block->label());

        return redirect()->route('admin.home-sections.index')->with('status', $block->typeName() . ' added at the bottom of the homepage. Drag it where you want it.');
    }

    public function edit(HomeBlock $homeBlock)
    {
        return $this->form($homeBlock);
    }

    public function update(Request $request, HomeBlock $homeBlock)
    {
        $this->fill($request, $homeBlock);
        $homeBlock->save();
        ActivityLogger::log('Homepage Section Updated', $homeBlock->typeName() . ': ' . $homeBlock->label());

        return redirect()->route('admin.home-sections.index')->with('status', $homeBlock->label() . ' saved.');
    }

    public function destroy(HomeBlock $homeBlock)
    {
        foreach ($homeBlock->imagePaths() as $path) {
            $this->forget($path);
        }
        $homeBlock->delete();
        ActivityLogger::log('Homepage Section Deleted', $homeBlock->typeName() . ': ' . $homeBlock->label());

        return redirect()->route('admin.home-sections.index')->with('status', $homeBlock->label() . ' deleted.');
    }

    private function form(HomeBlock $block)
    {
        return view('admin.home-blocks.form', [
            'block'      => $block,
            'categories' => Category::where('is_active', true)->orderBy('position')->orderBy('name')->get(['id', 'name', 'parent_id']),
        ]);
    }

    /** Sensible content for a new section, so the preview is never blank */
    private static function starter(string $type): array
    {
        return match ($type) {
            'products' => ['sort' => 'latest', 'limit' => 8],
            'promo_band' => ['bg' => '#111827', 'fg' => '#ffffff', 'button_text' => 'Shop now', 'link_url' => '/shop'],
            'card_grid' => ['columns' => 3, 'cards' => []],
            'stats_row' => ['stats' => [['value' => '10,000+', 'label' => 'Happy customers'], ['value' => '64', 'label' => 'Districts delivered'], ['value' => '4.8★', 'label' => 'Average rating']]],
            default => [],
        };
    }

    private function fill(Request $request, HomeBlock $block): void
    {
        $link = ['nullable', 'string', 'max:255'];
        $image = ['nullable', 'image', 'max:4096'];
        $color = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $rules = ['title' => ['nullable', 'string', 'max:120'], 'subtitle' => ['nullable', 'string', 'max:300']]
            + match ($block->type) {
                'products' => [
                    'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
                    'sort'        => ['required', Rule::in(array_keys(HomeBlock::SORTS))],
                    'limit'       => ['required', 'integer', 'min:2', 'max:24'],
                    'link_label'  => ['nullable', 'string', 'max:40'],
                    'link_url'    => $link,
                ],
                'promo_band' => [
                    'button_text' => ['nullable', 'string', 'max:40'],
                    'link_url'    => $link,
                    'bg'          => $color,
                    'fg'          => $color,
                ],
                'image_banner' => [
                    'link_url'          => $link,
                    'image_file'        => $image,
                    'image_mobile_file' => $image,
                ],
                'card_grid' => [
                    'columns'            => ['required', 'integer', 'min:2', 'max:4'],
                    'cards'              => ['nullable', 'array', 'max:' . HomeBlock::MAX_CARDS],
                    'cards.*.title'      => ['nullable', 'string', 'max:80'],
                    'cards.*.text'       => ['nullable', 'string', 'max:200'],
                    'cards.*.link'       => $link,
                    'cards.*.image_file' => $image,
                ],
                'stats_row' => [
                    'stats'         => ['nullable', 'array', 'max:' . HomeBlock::MAX_STATS],
                    'stats.*.value' => ['nullable', 'string', 'max:20'],
                    'stats.*.label' => ['nullable', 'string', 'max:60'],
                ],
            };

        $v = $request->validate($rules, ['bg.regex' => 'Pick a colour like #111827.', 'fg.regex' => 'Pick a colour like #ffffff.']);
        $old = $block->data ?? [];

        $block->title = $v['title'] ?? null;
        $block->subtitle = $v['subtitle'] ?? null;

        $block->data = match ($block->type) {
            'products' => [
                'category_id' => $v['category_id'] ?? null,
                'sort'        => $v['sort'],
                'limit'       => (int) $v['limit'],
                'link_label'  => $v['link_label'] ?? null,
                'link_url'    => $v['link_url'] ?? null,
            ],
            'promo_band' => [
                'button_text' => $v['button_text'] ?? null,
                'link_url'    => $v['link_url'] ?? null,
                'bg'          => $v['bg'] ?? '#111827',
                'fg'          => $v['fg'] ?? '#ffffff',
            ],
            'image_banner' => [
                'link_url'     => $v['link_url'] ?? null,
                'image'        => $this->picture($request, 'image_file', 'remove_image', $old['image'] ?? null),
                'image_mobile' => $this->picture($request, 'image_mobile_file', 'remove_image_mobile', $old['image_mobile'] ?? null),
            ],
            'card_grid' => [
                'columns' => (int) $v['columns'],
                'cards'   => $this->cards($request, $v['cards'] ?? [], $old['cards'] ?? []),
            ],
            'stats_row' => [
                'stats' => array_values(array_filter(array_map(fn ($s) => [
                    'value' => trim((string) ($s['value'] ?? '')),
                    'label' => trim((string) ($s['label'] ?? '')),
                ], $v['stats'] ?? []), fn ($s) => $s['value'] !== '')),
            ],
        };

        if ($block->type === 'image_banner' && empty($block->data['image'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['image_file' => 'Upload the banner picture.']);
        }
    }

    /** Keep, replace or remove one picture */
    private function picture(Request $request, string $field, string $remove, ?string $current): ?string
    {
        if ($request->hasFile($field)) {
            $this->forget($current);

            return $request->file($field)->store('home', 'public');
        }
        if ($request->boolean($remove)) {
            $this->forget($current);

            return null;
        }

        return $current;
    }

    private function cards(Request $request, array $input, array $old): array
    {
        $cards = [];
        foreach (array_slice($input, 0, HomeBlock::MAX_CARDS, true) as $i => $c) {
            $current = $old[$i]['image'] ?? null;
            $image = $request->hasFile("cards.$i.image_file")
                ? $request->file("cards.$i.image_file")->store('home', 'public')
                : ($request->boolean("cards.$i.remove_image") ? null : $current);
            if ($image !== $current) {
                $this->forget($current);
            }
            $card = [
                'image' => $image,
                'title' => trim((string) ($c['title'] ?? '')),
                'text'  => trim((string) ($c['text'] ?? '')),
                'link'  => trim((string) ($c['link'] ?? '')),
            ];
            if ($card['title'] !== '' || $card['image']) {
                $cards[] = $card;
            } else {
                $this->forget($image);
            }
        }

        return $cards;
    }

    private function forget(?string $path): void
    {
        if ($path && ! preg_match('#^(https?:)?//#', $path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
