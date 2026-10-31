<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>{{ __('Accept invitation') }} | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}">
    @vite(['resources/css/app.css'])
    <style>body { font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', Inter, 'Segoe UI Variable Text', system-ui, sans-serif; }</style>
</head>
<body class="flex min-h-full items-center justify-center bg-[#f5f5f7] px-4 py-12 text-[13px] text-[#1d1d1f] antialiased dark:bg-[#1c1c1e] dark:text-white">
<div class="w-full max-w-[360px]">
    <img src="{{ asset('images/calogo-dark.svg') }}" alt="CA.web" class="mx-auto h-8 dark:hidden">
    <img src="{{ asset('images/calogo.png') }}" alt="CA.web" class="mx-auto hidden h-8 dark:block">

    <div class="mt-8 rounded-2xl bg-white p-8 shadow-[0_0_0_0.5px_rgb(0_0_0/0.08),0_12px_40px_rgb(0_0_0/0.06)] dark:bg-[#2c2c2e] dark:shadow-[0_0_0_0.5px_rgb(255_255_255/0.08)]">
        <h1 class="text-[22px] font-bold tracking-[-0.02em]">{{ __('Welcome aboard') }}</h1>
        <p class="mt-1 text-[#86868b]">
            {{ __('Finish setting up the account for :email.', ['email' => $invitation->user->email]) }}
        </p>

        <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}" class="mt-6 space-y-4">
            @csrf

            @foreach ([
                ['name', __('Your name'), 'text', 'name'],
                ['password', __('Password'), 'password', 'new-password'],
                ['password_confirmation', __('Confirm password'), 'password', 'new-password'],
            ] as [$field, $label, $type, $autocomplete])
                <div>
                    <label for="{{ $field }}" class="block font-medium">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" required
                           value="{{ $type === 'text' ? old($field) : '' }}"
                           class="mt-1.5 block w-full rounded-lg border-0 bg-white px-3 py-2 shadow-[0_0_0_0.5px_rgb(0_0_0/0.15)] outline-none focus:shadow-[0_0_0_1px_#007aff,0_0_0_4px_rgb(0_122_255/0.3)] dark:bg-white/5 dark:shadow-[0_0_0_0.5px_rgb(255_255_255/0.15)]">
                    @error($field)
                        <p class="mt-1 text-[#ff3b30]">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit"
                    class="w-full rounded-lg bg-[#007aff] px-4 py-2 font-medium text-white shadow-[inset_0_0.5px_0_rgb(255_255_255/0.25)] hover:bg-[#0071eb] focus-visible:shadow-[0_0_0_4px_rgb(0_122_255/0.35)] focus:outline-none">
                {{ __('Create account') }}
            </button>
        </form>
    </div>
</div>
</body>
</html>
