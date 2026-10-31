<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Shop\Notifier;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

/**
 * Two boxes above the orders: are order emails and the Google Sheet switched on,
 * and did the last one work?
 */
class NotifyStatus extends Widget
{
    protected string $view = 'filament.widgets.notify-status';

    protected int|string|array $columnSpan = 'full';

    #[On('notify-status-changed')]
    public function refresh(): void {}

    protected function getViewData(): array
    {
        $notifier = app(Notifier::class);

        return ['boxes' => [
            [
                'title' => 'Order emails',
                'on' => $notifier->emailEnabled(),
                'onText' => 'New orders are emailed to '.config('shop.notify.email_to').' through '.$notifier->emailProvider().'.',
                'offText' => 'Orders are saved, but nobody is emailed. Set BREVO_API_KEY (or RESEND_API_KEY) and EMAIL_FROM on Railway.',
                ...$notifier->status('email'),
            ],
            [
                'title' => 'Google Sheet',
                'on' => $notifier->sheetEnabled(),
                'onText' => 'Every new order is added to the sheet straight away.',
                'offText' => 'Orders are not copied to a sheet. Set SHEETS_WEBHOOK_URL and SHEETS_TOKEN on Railway (see the README).',
                ...$notifier->status('sheet'),
            ],
        ]];
    }
}
