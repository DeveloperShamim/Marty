@if($gtmId = tracking_gtm_id())
<!-- Google Tag Manager -->
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtmId }}');
</script>
@endif

@php($ga4Id = tracking_ga4_id())
@php($adsId = tracking_google_ads_id())
@if($ga4Id || $adsId)
<!-- Google tag: Analytics 4 and Google Ads -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id ?: $adsId }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
@if($ga4Id)gtag('config', '{{ $ga4Id }}');
@endif
@if($adsId)gtag('config', '{{ $adsId }}');
@endif
</script>
@endif

@if($pixelId = tracking_meta_pixel_id())
<!-- Meta (Facebook) Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $pixelId }}');
fbq('track', 'PageView');
</script>
@endif

@if($gscCode = trim((string) setting('google_site_verification', '')))
<!-- Google Search Console Verification -->
<meta name="google-site-verification" content="{{ $gscCode }}" />
@endif

@if($tiktokId = tracking_tiktok_pixel_id())
<!-- TikTok Pixel -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=d.createElement("script");n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=d.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
  ttq.load('{{ $tiktokId }}');
  ttq.page();
}(window, document, 'ttq');
</script>
@endif

@if($fbDomain = tracking_meta_domain_verification())
<meta name="facebook-domain-verification" content="{{ $fbDomain }}" />
@endif

@if(tracking_any_enabled())
<script>
/* One call per shop event, sent to every tag that is set up (GTM data layer, GA4 and Google Ads, Meta Pixel,
   TikTok Pixel). d = { items: [{item_id, item_name, item_variant, price, quantity}], value, event_id,
   transaction_id }. Meta and TikTok get the event id so the server-side copy of a purchase is counted once. */
window.vtTrack = function (name, d) {
  try {
    var cfg = {!! json_encode([
      'currency' => setting('currency_code', 'BDT'),
      'dataLayer' => (bool) (tracking_gtm_id() || tracking_ga4_id()),
      'adsPurchase' => tracking_google_ads_purchase(),
    ], JSON_UNESCAPED_SLASHES) !!};
    var items = (d.items || []).map(function (i) { return { item_id: String(i.item_id), item_name: i.item_name, item_variant: i.item_variant || undefined, price: Number(i.price) || 0, quantity: Number(i.quantity) || 1 }; });
    var value = d.value != null ? Number(d.value) : items.reduce(function (s, i) { return s + i.price * i.quantity; }, 0);
    var ecommerce = { currency: cfg.currency, value: value, items: items };
    if (d.transaction_id) { ecommerce.transaction_id = d.transaction_id; ecommerce.tax = d.tax; ecommerce.shipping = d.shipping; ecommerce.coupon = d.coupon || undefined; }

    if (cfg.dataLayer) { window.dataLayer = window.dataLayer || []; window.dataLayer.push({ ecommerce: null }); window.dataLayer.push({ event: name, ecommerce: ecommerce }); }
    if (typeof gtag === 'function') {
      gtag('event', name, ecommerce);
      if (name === 'purchase' && cfg.adsPurchase) gtag('event', 'conversion', { send_to: cfg.adsPurchase, value: value, currency: cfg.currency, transaction_id: d.transaction_id });
    }

    var meta = { view_item: 'ViewContent', add_to_cart: 'AddToCart', begin_checkout: 'InitiateCheckout', purchase: 'Purchase' }[name];
    if (meta && typeof fbq === 'function') {
      fbq('track', meta, {
        content_type: 'product', currency: cfg.currency, value: value,
        content_ids: items.map(function (i) { return i.item_id; }),
        contents: items.map(function (i) { return { id: i.item_id, quantity: i.quantity, item_price: i.price }; }),
        content_name: items.length === 1 ? items[0].item_name : undefined,
        num_items: items.reduce(function (s, i) { return s + i.quantity; }, 0)
      }, d.event_id ? { eventID: d.event_id } : undefined);
    }

    var tt = { view_item: 'ViewContent', add_to_cart: 'AddToCart', begin_checkout: 'InitiateCheckout', purchase: 'CompletePayment' }[name];
    if (tt && window.ttq && typeof ttq.track === 'function') {
      ttq.track(tt, {
        content_type: 'product', currency: cfg.currency, value: value,
        contents: items.map(function (i) { return { content_id: i.item_id, content_name: i.item_name, quantity: i.quantity, price: i.price }; })
      }, d.event_id ? { event_id: d.event_id } : undefined);
    }
  } catch (e) { /* tracking must never break the shop */ }
};
</script>
@endif

@if(($customHead = (string) setting('tracking_custom_head', '')) !== '')
{!! $customHead !!}
@endif

@stack('tracking-head')
