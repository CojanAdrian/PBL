<footer class="border-t border-gray-900/5 bg-gray-50">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 py-14 md:grid-cols-3">
        <div>
            <p class="text-lg font-semibold tracking-tight">@text('site_name', 'Studio Nord')</p>
            <p class="mt-3 max-w-xs text-sm text-gray-600">@text('footer_tagline', 'A small design and development studio building fast, friendly websites.')</p>
        </div>

        <div class="text-sm">
            <p class="font-semibold">@text('footer_contact_title', 'Contact')</p>
            <ul class="mt-3 space-y-2 text-gray-600">
                <li>@text('contact_email', 'hello@studionord.com')</li>
                <li>@text('contact_phone', '+373 60 000 000')</li>
                <li>@text('contact_address', 'Chișinău, Moldova')</li>
            </ul>
        </div>

        <div class="text-sm">
            <p class="font-semibold">@text('footer_links_title', 'Information')</p>
            <ul class="mt-3 space-y-2 text-gray-600">
                <li><a href="{{ route('front.faq', app()->getLocale()) }}" class="hover:text-gray-950">@text('footer_link_faq', 'FAQ')</a></li>
                <li><a href="{{ route('front.privacy', app()->getLocale()) }}" class="hover:text-gray-950">@text('footer_link_privacy', 'Privacy policy')</a></li>
                <li><a href="{{ route('front.terms', app()->getLocale()) }}" class="hover:text-gray-950">@text('footer_link_terms', 'Terms & conditions')</a></li>
            </ul>
        </div>
    </div>

    <p class="border-t border-gray-900/5 py-6 text-center text-xs text-gray-500">
        © {{ date('Y') }} @text('footer_copyright', 'Studio Nord. All rights reserved.')
    </p>
</footer>
