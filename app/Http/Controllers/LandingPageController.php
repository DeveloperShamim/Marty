<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\Product;
use App\Services\FreeDelivery;
use App\Services\OrderPlacer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The public offer page /offer/{slug} and its order form (Marketing → Landing pages). */
class LandingPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = $this->find($request, $slug);
        $product = $page->product;

        // One view per visitor session; staff previews don't count
        $key = 'landing_viewed.' . $page->id;
        if (! $this->isStaff($request) && ! $request->session()->has($key)) {
            $request->session()->put($key, true);
            LandingPage::whereKey($page->id)->increment('views');
        }

        // Staff can try another template before switching to it: /offer/slug?template=bold (nothing is saved)
        $trying = (string) $request->query('template', '');
        if ($trying !== '' && isset(LandingPage::TEMPLATES[$trying]) && $this->isStaff($request)) {
            if ($page->template() !== $trying && strcasecmp((string) $page->accent_color, LandingPage::TEMPLATES[$page->template()][2]) === 0) {
                $page->accent_color = null; // the old template's own colour follows the template
            }
            $page->template = $trying;
        }

        return view('storefront.landing', [
            'page'      => $page,
            'trying'    => $trying !== '' && $page->template === $trying && $this->isStaff($request),
            'd'         => $page->data(),
            'product'   => $product,
            'gallery'   => $page->galleryUrls(),
            'specRows'  => $page->specRows(),
            'groups'    => self::optionGroups($product),
            'skus'      => self::skuPayload($product),
            'quotes'    => self::quotes($page),
            'maxQty'    => LandingPage::MAX_QTY,
            'preview'   => ! $page->is_published,
        ]);
    }

    public function order(Request $request, string $slug, OrderPlacer $orders)
    {
        $page = $this->find($request, $slug);
        abort_unless($page->is_published, 404);
        $product = $page->product;

        if ($request->filled('customer_phone')) {
            $request->merge(['customer_phone' => preg_replace('/[\s\-\(\)]/', '', (string) $request->input('customer_phone'))]);
        }

        $hasOptions = $product->skus->where('is_active', true)->isNotEmpty();

        $data = $request->validate([
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_phone'   => ['required', 'string', 'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'city'             => ['nullable', 'string', 'max:80'],
            'shipping_zone'    => ['required', Rule::in(['inside_dhaka', 'outside_dhaka'])],
            'delivery_note'    => ['nullable', 'string', 'max:500'],
            'qty'              => ['required', 'integer', 'min:1', 'max:' . LandingPage::MAX_QTY],
            'sku_id'           => $hasOptions
                ? ['required', 'integer', Rule::exists('product_skus', 'id')->where('product_id', $product->id)->where('is_active', true)]
                : ['nullable'],
        ], [
            'customer_name.required'    => 'আপনার নাম লিখুন।',
            'customer_phone.required'   => 'আপনার মোবাইল নম্বর লিখুন।',
            'customer_phone.regex'      => 'সঠিক ১১ সংখ্যার মোবাইল নম্বর লিখুন (যেমন 01712345678)।',
            'shipping_address.required' => 'আপনার ঠিকানা লিখুন।',
            'shipping_zone.required'    => 'ঢাকার ভিতরে না বাইরে, বেছে নিন।',
            'sku_id.required'           => 'সাইজ / কালার বেছে নিন।',
            'sku_id.exists'             => 'এই সাইজ / কালারটি এখন পাওয়া যাচ্ছে না।',
        ]);

        $data['customer_phone'] = CheckoutController::normalizeBdPhone($data['customer_phone']) ?? $data['customer_phone'];
        $data['payment_method'] = 'cod';

        $qty = (int) $data['qty'];
        $sku = $hasOptions ? $product->skus->firstWhere('id', (int) $data['sku_id']) : null;
        $price = $page->price();
        $subtotal = $price * $qty;

        $item = (object) [
            'product'    => $product,
            'product_id' => $product->id,
            'sku_id'     => $sku?->id,
            'name'       => $product->name,
            'variant'    => $sku?->attributeLabel(),
            'price'      => $price,
            'qty'        => $qty,
            'line_total' => $subtotal,
        ];

        $totals = self::totals($subtotal, $data['shipping_zone'], (bool) $product->free_delivery);

        try {
            $order = $orders->place($data, collect([$item]), $totals, null, $request, ['landing_page_id' => $page->id]);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['order' => 'দুঃখিত, এই পরিমাণ এখন স্টকে নেই। পরিমাণ বা সাইজ বদলে আবার চেষ্টা করুন।']);
        }

        return redirect()->route('order.confirmation', $order->order_number);
    }

    /** Published pages for everyone; drafts only for signed-in staff (preview). */
    private function find(Request $request, string $slug): LandingPage
    {
        $page = LandingPage::with(['product.images', 'product.skus'])->where('slug', $slug)->first();

        abort_unless($page && $page->product, 404);
        abort_unless($page->is_published || $this->isStaff($request), 404);

        return $page;
    }

    private function isStaff(Request $request): bool
    {
        $user = $request->user();

        return $user && method_exists($user, 'isStaff') && $user->isStaff();
    }

    /** Delivery, tax and total for an order of $subtotal, paid cash on delivery. */
    public static function totals(float $subtotal, string $zone, bool $freeProduct): array
    {
        $delivery = FreeDelivery::quote($subtotal, $zone, 'cod', $freeProduct);
        $tax = round($subtotal * (float) setting('tax_percent', 0) / 100, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => 0.0,
            'shipping' => $delivery['shipping'],
            'waived'   => $delivery['waived'],
            'reason'   => $delivery['reason'],
            'tax'      => $tax,
            'total'    => $subtotal + $delivery['shipping'] + $tax,
        ];
    }

    /** Every quantity × zone the form can show, worked out here so the page shows exactly what the order will charge. */
    private static function quotes(LandingPage $page): array
    {
        $out = [];
        $free = (bool) $page->product->free_delivery;
        for ($q = 1; $q <= LandingPage::MAX_QTY; $q++) {
            foreach (['inside_dhaka', 'outside_dhaka'] as $zone) {
                $t = self::totals($page->price() * $q, $zone, $free);
                $out[$q][$zone] = ['subtotal' => $t['subtotal'], 'shipping' => $t['shipping'], 'tax' => $t['tax'], 'total' => $t['total']];
            }
        }

        return $out;
    }

    /** Option buttons (e.g. Size, Color) from the product's active SKUs. */
    public static function optionGroups(Product $product): array
    {
        $groups = [];
        foreach ($product->skus->where('is_active', true) as $sku) {
            foreach ($sku->getAttributesData() as $k => $v) {
                $k = trim((string) $k);
                $v = trim((string) $v);
                if ($k !== '' && $v !== '' && ! in_array($v, $groups[$k] ?? [], true)) {
                    $groups[$k][] = $v;
                }
            }
        }

        return $groups;
    }

    public static function skuPayload(Product $product): array
    {
        return $product->skus->where('is_active', true)->map(fn ($sku) => [
            'id'    => $sku->id,
            'attrs' => collect($sku->getAttributesData())->mapWithKeys(fn ($v, $k) => [trim((string) $k) => trim((string) $v)])->all(),
            'stock' => (int) $sku->stock_quantity,
        ])->values()->all();
    }
}
