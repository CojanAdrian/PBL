<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Widgets\NotifyStatus;
use App\Models\Order;
use App\Shop\Notifier;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Throwable;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    public function getSubheading(): ?string
    {
        return 'Every preorder from the website. Keep track of each order\'s status in the Google Sheet.';
    }

    protected function getHeaderWidgets(): array
    {
        return [NotifyStatus::class];
    }

    protected function getHeaderActions(): array
    {
        $notifier = app(Notifier::class);

        $sheetUrl = $notifier->sheetOpenUrl();

        return [
            Action::make('openSheet')
                ->label('Open Google Sheet')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->visible(filled($sheetUrl))
                ->url($sheetUrl, shouldOpenInNewTab: true),
            Action::make('syncSheet')
                ->label('Sync all to Google Sheet')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('gray')
                ->visible(fn () => $notifier->sheetEnabled())
                ->requiresConfirmation()
                ->modalDescription('Adds every order the sheet doesn\'t have yet. Rows already in the sheet are left exactly as they are.')
                ->action(function () use ($notifier) {
                    try {
                        $result = $notifier->sheetAdd(Order::orderBy('created_at')->get());
                        Notification::make()->success()
                            ->title(($result['added'] ?? 0).' missing orders added')
                            ->body('All '.Order::count().' orders are in the sheet now.')
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()->danger()->title('The sync failed')->body($e->getMessage())->persistent()->send();
                    }
                    $this->dispatch('notify-status-changed');
                }),
            Action::make('testEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->visible(fn () => $notifier->emailEnabled())
                ->action(function () use ($notifier) {
                    try {
                        $notifier->sendEmail(
                            "Test email from Big Ed's Jerky",
                            'If you can read this, preorder emails are working. New preorders will arrive in this inbox.',
                            '<p style="font-family:Arial,sans-serif;font-size:16px">If you can read this, <strong>preorder emails are working</strong>. New preorders will arrive in this inbox.</p>',
                        );
                        Notification::make()->success()->title('Test email sent to '.config('shop.notify.email_to'))->body('Check the inbox, and the spam folder too.')->send();
                    } catch (Throwable $e) {
                        Notification::make()->danger()->title('The test email failed')->body($e->getMessage())->persistent()->send();
                    }
                    $this->dispatch('notify-status-changed');
                }),
        ];
    }
}
