@php
    $seoMetaRendered = true;
    $seo = $seo ?? \Modules\Meta\Support\PageSeo::for($page, $fallbackImageUrl ?? null);
    $title = $seo->title();
    $description = $seo->description();
    $canonical = $seo->canonicalUrl();
    $image = $seo->imageUrl();
    $robots = $seo->robots();
    $siteName = $seo->siteName();
    $twitterCard = $seo->twitterCard();
@endphp

<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $robots }}">
<meta name="title" content="{{ $title }}">
<meta name="description" content="{{ $description }}">

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ locale() }}">

@foreach (supported_locale_keys() as $code)
    <meta property="og:locale:alternate" content="{{ $code }}">
@endforeach

@if ($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:secure_url" content="{{ $image }}">
    <meta property="og:image:alt" content="{{ $title }}">
@endif

<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">

@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
    <meta name="twitter:image:alt" content="{{ $title }}">
@endif

<script type="application/ld+json">{!! json_encode($seo->structuredData(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
