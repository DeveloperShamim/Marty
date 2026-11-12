<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/** A store page (About us, FAQ, Shipping, Returns…) written in Admin → Pages, shown at /page/{slug}. */
class Page extends Model
{
    /** Slugs the store already uses for its own pages. */
    public const RESERVED = ['contact', 'terms', 'privacy'];

    protected $fillable = ['title', 'slug', 'body', 'meta_description', 'is_published', 'show_in_footer', 'position'];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_footer' => 'boolean',
        'position' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeInFooter(Builder $q): Builder
    {
        return $q->published()->where('show_in_footer', true)->orderBy('position')->orderBy('id');
    }

    public function url(): string
    {
        return route('page', $this->slug);
    }

    /**
     * The body as safe HTML from simple text: a blank line starts a new paragraph, "## " a heading,
     * "- " a bullet, **bold**. Everything else is escaped, so no HTML from the editor reaches the page.
     */
    public function bodyHtml(): HtmlString
    {
        $html = [];
        foreach (preg_split('/\R{2,}/', trim((string) $this->body)) as $block) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $block)), fn ($l) => $l !== ''));
            if ($lines === []) {
                continue;
            }
            if (str_starts_with($lines[0], '## ')) {
                $html[] = '<h2>' . self::inline(substr(array_shift($lines), 3)) . '</h2>';
                if ($lines === []) {
                    continue;
                }
            }
            if (collect($lines)->every(fn ($l) => str_starts_with($l, '- '))) {
                $html[] = '<ul>' . collect($lines)->map(fn ($l) => '<li>' . self::inline(substr($l, 2)) . '</li>')->implode('') . '</ul>';
            } else {
                $html[] = '<p>' . collect($lines)->map(fn ($l) => self::inline($l))->implode('<br>') . '</p>';
            }
        }

        return new HtmlString(implode("\n", $html));
    }

    private static function inline(string $text): string
    {
        return preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($text));
    }
}
