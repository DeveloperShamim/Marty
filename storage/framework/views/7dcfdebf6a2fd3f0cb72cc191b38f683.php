<?php
    $siteName     = site_name();
    $pageTitle    = trim($rawTitle ?? '') !== ''
        ? $rawTitle
        : (trim($title ?? '') !== '' ? ($title . ' — ' . $siteName) : setting('default_meta_title', $siteName));
    $desc         = $metaDescription ?? setting('default_meta_description', $siteName);
    $keywords     = $metaKeywords ?? setting('default_meta_keywords');
    $canonicalUrl = $canonical ?? url()->current();
    $image        = $ogImage ?? null;
?>
<title><?php echo e($pageTitle); ?></title>
<meta name="description" content="<?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $desc), 300)); ?>">
<?php if($keywords): ?><meta name="keywords" content="<?php echo e($keywords); ?>"><?php endif; ?>
<link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
<meta name="robots" content="index, follow">

<meta property="og:type" content="<?php echo e($ogType ?? 'website'); ?>">
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $desc), 300)); ?>">
<meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
<meta property="og:site_name" content="<?php echo e($siteName); ?>">
<?php if($image): ?><meta property="og:image" content="<?php echo e($image); ?>"><?php endif; ?>

<meta name="twitter:card" content="<?php echo e($image ? 'summary_large_image' : 'summary'); ?>">
<meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
<meta name="twitter:description" content="<?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $desc), 200)); ?>">
<?php if($image): ?><meta name="twitter:image" content="<?php echo e($image); ?>"><?php endif; ?>

<?php if(isset($jsonLd)): ?>
<script type="application/ld+json"><?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endif; ?>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/partials/seo.blade.php ENDPATH**/ ?>