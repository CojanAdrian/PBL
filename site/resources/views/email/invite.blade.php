@component('mail::message')
# You're invited

You have been invited to the admin panel of **{{ config('app.name') }}**. Use the button below to choose your name and password.

@component('mail::button', ['url' => $url])
Accept invitation
@endcomponent

If you were not expecting this invitation, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
