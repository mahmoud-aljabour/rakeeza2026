@php
    $canonical = $url ?? url()->current();
    $shareImage = isset($image) ? (string) $image : '';

    if ($shareImage !== '' && ! str_starts_with($shareImage, 'http://') && ! str_starts_with($shareImage, 'https://')) {
        $shareImage = url($shareImage);
    }
@endphp
@if ($description !== '')
    <meta name="description" content="{{ $description }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
@if ($description !== '')
    <meta property="og:description" content="{{ $description }}">
@endif
@if ($shareImage !== '')
    <meta property="og:image" content="{{ $shareImage }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
