@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    {{--
        DEMO HOME PAGE - replace it with the client's design.
        Every @text / @image below is editable from Admin → Website → Visual editor:
            @text('key', 'Default text')
            <img @image('key', 'path/in/public.jpg') alt="">
    --}}

    {{-- Hero --}}
    <section class="mx-auto grid max-w-6xl items-center gap-14 px-6 pt-20 pb-24 md:grid-cols-2">
        <div>
            <p class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold tracking-wide text-gray-600">@text('home_eyebrow', 'Design & development studio')</p>
            <h1 class="mt-5 text-4xl font-semibold tracking-tight text-balance md:text-6xl">@text('home_title', 'We build websites people love to use')</h1>
            <p class="mt-6 text-lg text-gray-600">@text('home_intro', 'Fast, beautiful websites for small businesses, from the first sketch to launch day and beyond.')</p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('front.contact') }}" class="rounded-full bg-gray-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">@text('home_cta_primary', 'Start a project')</a>
                <a href="{{ route('front.service') }}" class="rounded-full px-6 py-3 text-sm font-semibold text-gray-950 ring-1 ring-gray-900/10 transition hover:bg-gray-50">@text('home_cta_secondary', 'See our services')</a>
            </div>

            <dl class="mt-12 grid grid-cols-3 gap-6 border-t border-gray-900/5 pt-8">
                @foreach ([1 => ['120+', 'Websites launched'], 2 => ['9', 'Years in business'], 3 => ['4.9', 'Average rating']] as $n => [$value, $label])
                    <div>
                        <dt class="text-3xl font-semibold tracking-tight">@text("home_stat_{$n}_value", $value)</dt>
                        <dd class="mt-1 text-sm text-gray-500">@text("home_stat_{$n}_label", $label)</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <img @image('home_hero', 'images/demo.svg') alt="" class="aspect-[4/5] w-full rounded-[2rem] object-cover shadow-2xl shadow-sky-900/10">
    </section>

    {{-- Features --}}
    <section class="bg-gray-50 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-semibold tracking-tight md:text-4xl">@text('home_features_title', 'Everything your website needs')</h2>
                <p class="mt-4 text-gray-600">@text('home_features_intro', 'We take care of the details so you can focus on running your business.')</p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ([
                    1 => ['Thoughtful design', 'Clean layouts that put your content first and look great on every screen.'],
                    2 => ['Built for speed', 'Pages load in a blink, which keeps visitors happy and helps you rank on Google.'],
                    3 => ['Easy to update', 'Change any text or picture yourself, right on the page, whenever you like.'],
                ] as $n => [$title, $text])
                    <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5">
                        <span class="grid size-11 place-items-center rounded-2xl bg-gray-950 text-sm font-semibold text-white">0{{ $n }}</span>
                        <h3 class="mt-6 text-lg font-semibold">@text("home_feature_{$n}_title", $title)</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-600">@text("home_feature_{$n}_text", $text)</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- About --}}
    <section class="mx-auto grid max-w-6xl items-center gap-14 px-6 py-24 md:grid-cols-2">
        <img @image('home_about', 'images/demo-about.svg') alt="" class="aspect-square w-full rounded-[2rem] object-cover">

        <div>
            <h2 class="text-3xl font-semibold tracking-tight md:text-4xl">@text('home_about_title', 'A small team that cares about the details')</h2>
            <p class="mt-5 text-gray-600">@text('home_about_text', "We are designers and developers who love building for the web.\nEvery project gets our full attention, from the first call to long after launch.")</p>

            <ul class="mt-8 space-y-3 text-sm">
                @foreach ([1 => 'Personal support from the people who build your site', 2 => 'Clear prices, no surprises', 3 => 'Your website, your content, always in your control'] as $n => $point)
                    <li class="flex gap-3">
                        <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-emerald-500 text-[10px] text-white">✓</span>
                        <span>@text("home_about_point_{$n}", $point)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Testimonial --}}
    <section class="mx-auto max-w-4xl px-6 pb-24 text-center">
        <img @image('home_work', 'images/demo-work.svg') alt="" class="aspect-[21/9] w-full rounded-[2rem] object-cover">
        <blockquote class="mt-14 text-2xl font-medium tracking-tight text-balance md:text-3xl">
            “@text('home_quote', 'They rebuilt our website in three weeks and our bookings doubled. Updating it ourselves is easy.')”
        </blockquote>
        <div class="mt-8 flex items-center justify-center gap-3">
            <img @image('home_quote_avatar', 'images/demo-avatar.svg') alt="" class="size-12 rounded-full object-cover">
            <div class="text-start text-sm">
                <p class="font-semibold">@text('home_quote_author', 'Elena Moraru')</p>
                <p class="text-gray-500">@text('home_quote_role', 'Owner, Casa Verde Café')</p>
            </div>
        </div>
    </section>

    {{-- Call to action --}}
    <section class="px-6 pb-24">
        <div class="mx-auto max-w-6xl rounded-[2rem] bg-gray-950 px-8 py-16 text-center text-white md:px-16">
            <h2 class="text-3xl font-semibold tracking-tight md:text-4xl">@text('home_final_title', 'Ready for a website you are proud of?')</h2>
            <p class="mx-auto mt-4 max-w-xl text-gray-300">@text('home_final_text', 'Tell us about your business and we will get back to you within one working day.')</p>
            <a href="{{ route('front.contact') }}" class="mt-8 inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-gray-950 transition hover:bg-gray-100">@text('home_final_cta', 'Get in touch')</a>
        </div>
    </section>

@endsection
