@php
    /** @var \Modules\Meta\Support\OpenGraph $openGraph */
    $title = $openGraph->title;
    $description = $openGraph->description();
    $canonical = $openGraph->url;
    $image = $openGraph->image;
    $siteName = $openGraph->siteName;
    $twitterCard = $openGraph->twitterCard();
@endphp

<link rel="canonical" href="{{ $canonical }}">
<meta name="description" content="{{ $description }}">

<meta property="og:type" content="{{ $openGraph->type }}">
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
    <meta property="og:image:alt" content="{{ $openGraph->imageAlt }}">
    @if ($imageMime = $openGraph->imageMimeType())
        <meta property="og:image:type" content="{{ $imageMime }}">
    @endif
@endif

@if ($openGraph->type === 'product' && $openGraph->priceAmount && $openGraph->priceCurrency)
    <meta property="product:price:amount" content="{{ $openGraph->priceAmount }}">
    <meta property="product:price:currency" content="{{ $openGraph->priceCurrency }}">
@endif

<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">

@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
    <meta name="twitter:image:alt" content="{{ $openGraph->imageAlt }}">
@endif
