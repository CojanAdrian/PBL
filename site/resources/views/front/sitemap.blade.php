{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($codes as $code)
    <url>
        <loc>{{ route('front.index', ['locale' => $code]) }}</loc>
@foreach ($codes as $other)
        <xhtml:link rel="alternate" hreflang="{{ $other }}" href="{{ route('front.index', ['locale' => $other]) }}"/>
@endforeach
    </url>
@endforeach
</urlset>
