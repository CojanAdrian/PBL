<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | @yield('title')</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}">

    @include('front.inc.meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
