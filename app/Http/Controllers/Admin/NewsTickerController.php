<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Marketing → News Ticker: the scrolling headline bar at the top of every storefront page. */
class NewsTickerController extends Controller
{
    public function index()
    {
        $endsAt = setting('flash_sale_ends_at');
        try {
            $flashEnds = $endsAt ? Carbon::parse($endsAt) : null;
        } catch (\Throwable) {
            $flashEnds = null;
        }

        return view('admin.news-ticker.index', [
            // Headlines used to be stored "||"-separated on one line; show them one per line.
            'headlines'     => preg_replace('/\s*\|\|\s*/', "\n", (string) setting('header_promo_text', '')),
            'label'         => (string) setting('ticker_label', 'Hot Deals'),
            'labelStyle'    => (string) setting('ticker_label_style', 'dark'),
            'link'          => (string) setting('header_promo_link', ''),
            'showCountdown' => setting('ticker_show_countdown', '1') === '1',
            'flashEnds'     => $flashEnds && $flashEnds->isFuture() ? $flashEnds : null,
            'theme'         => generate_3_color_matching_theme(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'header_promo_text'  => ['nullable', 'string', 'max:1000'],
            'ticker_label'       => ['nullable', 'string', 'max:24'],
            'ticker_label_style' => ['required', 'in:dark,red,white'],
            'header_promo_link'  => ['nullable', 'string', 'max:255'],
        ]);

        $headlines = collect(preg_split('/\R|\|\|/', (string) ($data['header_promo_text'] ?? '')))
            ->map(fn ($line) => trim($line))->filter()->implode("\n");

        Setting::put('header_promo_text', $headlines);
        Setting::put('ticker_label', trim((string) ($data['ticker_label'] ?? '')));
        Setting::put('ticker_label_style', $data['ticker_label_style']);
        Setting::put('header_promo_link', trim((string) ($data['header_promo_link'] ?? '')));
        Setting::put('ticker_show_countdown', $request->boolean('ticker_show_countdown') ? '1' : '0');
        Setting::forgetCache();

        ActivityLogger::log('News Ticker Updated', 'Updated the storefront news ticker headlines.');

        return back()->with('status', 'News ticker saved. It is live on the storefront now.');
    }
}
