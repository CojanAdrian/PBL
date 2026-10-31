@extends('layouts.front')
@section('title', __('Thank you'))
@section('content')

    <section class="flex min-h-[70vh] items-center justify-center px-6 py-24">
        <div class="max-w-md text-center">
            <div class="mx-auto flex size-16 animate-[bounce_1s_ease-out_1] items-center justify-center rounded-full bg-violet-100 text-violet-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-semibold tracking-tight">{{ __('Message send successfully') }}</h1>
            <p class="mt-3 text-gray-600">{{ __('We will contact you soon!') }}</p>

            <a href="{{ route('front.index') }}"
               class="mt-8 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">
                {{ __('Go Home') }}
            </a>
        </div>
    </section>

@endsection
