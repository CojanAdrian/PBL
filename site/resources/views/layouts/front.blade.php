<!doctype html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">

@include('front.inc.header')

<body class="min-h-screen bg-white font-sans text-gray-900 antialiased">

@include('front.inc.menu')

<main>
    @yield('content')
</main>

@include('front.inc.footer')

<div x-data="cookieConsent" x-show="open" x-cloak x-transition.opacity
     class="fixed inset-x-4 bottom-4 z-50 mx-auto flex max-w-3xl flex-col gap-3 rounded-2xl bg-gray-900/95 p-4 text-sm text-gray-200 shadow-xl backdrop-blur sm:flex-row sm:items-center">
    <p class="flex-1">
        {{ __('To provide you with the best website experience, we use ') }}
        <a href="{{ route('front.privacy') }}" class="underline hover:text-white" target="_blank">{{ __('cookies.') }}</a>
    </p>
    <div class="flex gap-2">
        <button type="button" @click="dismiss" class="rounded-lg px-3 py-1.5 hover:text-white">&times;</button>
        <button type="button" @click="accept" class="rounded-lg bg-violet-500 px-4 py-1.5 font-semibold text-white hover:bg-violet-400">
            {{ __('Accept') }}
        </button>
    </div>
</div>

</body>
</html>
