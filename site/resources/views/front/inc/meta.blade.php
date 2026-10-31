@isset($meta)
    @php($metaImage = $meta->img ? Storage::disk('page')->url($meta->img) : null)

    <meta name="description" content="{{ $meta->description }}">
    <meta name="keywords" content="{{ $meta->keywords }}">
    <meta name="robots" content="index,follow">

    <!-- Open Graph -->
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $meta->title }}">
    <meta property="og:description" content="{{ $meta->description }}">
    @if($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta->title }}">
    <meta name="twitter:description" content="{{ $meta->description }}">
    @if($metaImage)
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif
@endisset
