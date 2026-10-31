@component('mail::message')
# New message from the website

**{{ $data['name'] }}** ({{ $data['email'] }}, {{ $data['phone'] }}) wrote:

> **{{ $data['subject'] }}**
>
> {{ $data['message'] }}

@component('mail::button', ['url' => \App\Filament\Resources\Inboxes\InboxResource::getUrl()])
Open inbox
@endcomponent

{{ config('app.name') }}
@endcomponent
