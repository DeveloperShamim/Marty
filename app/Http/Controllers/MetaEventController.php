<?php

namespace App\Http\Controllers;

use App\Jobs\SendServerMetaEvent;
use App\Services\ServerTracking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The browser posts PageView, ViewContent, AddToCart and InitiateCheckout here with the pixel's event id, and
 * the server sends the same event to Meta's Conversions API. It goes through the shop's own domain, so it still
 * arrives when an ad-blocker stops the pixel; Meta counts it once because the event ids match.
 */
class MetaEventController extends Controller
{
    public function __invoke(Request $request, ServerTracking $tracking)
    {
        if (! tracking_meta_capi_ready() || preg_match(ServerTracking::BOT_AGENTS, (string) $request->userAgent()) || ! $request->userAgent()) {
            return response()->noContent();
        }

        $data = $request->validate([
            'event'                  => ['required', Rule::in(ServerTracking::META_BROWSER_EVENTS)],
            'event_id'               => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9._-]+$/'],
            'url'                    => ['nullable', 'string', 'max:1000'],
            'custom'                 => ['nullable', 'array'],
            'custom.value'           => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'custom.content_name'    => ['nullable', 'string', 'max:200'],
            'custom.num_items'       => ['nullable', 'integer', 'min:0', 'max:10000'],
            'custom.content_ids'     => ['nullable', 'array', 'max:50'],
            'custom.content_ids.*'   => ['string', 'max:64'],
            'custom.contents'        => ['nullable', 'array', 'max:50'],
            'custom.contents.*.id'   => ['required', 'string', 'max:64'],
            'custom.contents.*.quantity'   => ['nullable', 'integer', 'min:1', 'max:10000'],
            'custom.contents.*.item_price' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
        ]);

        $context = ServerTracking::context($request);
        // Only pages of this shop count as the event's page
        $url = (string) ($data['url'] ?? '');
        $context['url'] = str_starts_with($url, url('/')) ? $url : url('/');

        $custom = $data['custom'] ?? [];
        if (isset($custom['contents'])) {
            $custom['contents'] = array_map(fn ($c) => array_filter([
                'id'         => (string) $c['id'],
                'quantity'   => isset($c['quantity']) ? (int) $c['quantity'] : null,
                'item_price' => isset($c['item_price']) ? (float) $c['item_price'] : null,
            ], fn ($v) => $v !== null), $custom['contents']);
        }

        SendServerMetaEvent::dispatchAfterResponse(
            $tracking->metaBrowserEvent($data['event'], $data['event_id'], $custom, $context, $request->user())
        );

        return response()->noContent();
    }
}
