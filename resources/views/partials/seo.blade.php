@php
    $siteName     = site_name();
    $pageTitle    = trim($rawTitle ?? '') !== ''
        ? $rawTitle
        : (trim($title ?? '') !== '' ? ($title . ' — ' . $siteName) : setting('default_meta_title', $siteName));
    $desc         = $metaDescription ?? setting('default_meta_description', $siteName);
    $keywords     = $metaKeywords ?? setting('default_meta_keywords');
    $canonicalUrl = $canonical ?? url()->current();
    $image        = $ogImage ?? null;
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $desc), 300) }}">
@if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="robots" content="index, follow">

<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $desc), 300) }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
@if($image)<meta property="og:image" content="{{ $image }}">@endif

<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $desc), 200) }}">
@if($image)<meta name="twitter:image" content="{{ $image }}">@endif

@isset($jsonLd)
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endisset
