<?php

namespace App\Filament\Resources\Inboxes;

use App\Filament\Resources\Inboxes\Pages\ManageInboxes;
use App\Filament\Support\Resource;
use App\Models\Inbox;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InboxResource extends Resource
{
    /** Not used by the Big Ed's site, so it stays out of the menu (the section still works). */
    protected static bool $shouldRegisterNavigation = false;

    /** Every active user can read contact form messages. */
    protected static bool $adminOnly = false;

    protected static ?string $model = Inbox::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationLabel = 'Inbox';

    protected static ?string $modelLabel = 'message';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getNavigationBadge(): ?string
    {
        $count = Inbox::unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Unread messages';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable()->url(fn (Inbox $record) => "mailto:{$record->email}"),
                TextEntry::make('phone')->copyable()->url(fn (Inbox $record) => "tel:{$record->phone}"),
            ]),
            Section::make(fn (Inbox $record) => $record->subject)
                ->description(fn (Inbox $record) => $record->created_at?->toDayDateTimeString())
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('message')->hiddenLabel()->prose(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (Inbox $record) => $record->read_at ? null : 'font-semibold')
            ->columns([
                TextColumn::make('name')->searchable()
                    ->description(fn (Inbox $record) => $record->email)
                    ->icon(fn (Inbox $record) => $record->read_at ? null : Heroicon::Envelope)
                    ->iconColor('primary'),
                TextColumn::make('subject')->searchable()->limit(50)
                    ->description(fn (Inbox $record) => str($record->message)->limit(80)),
                TextColumn::make('created_at')->label('Received')->since()->sortable()
                    ->tooltip(fn (Inbox $record) => $record->created_at?->toDayDateTimeString()),
            ])
            ->filters([
                TernaryFilter::make('read_at')->label('Read')->nullable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalFooterActions(fn (Inbox $record) => [
                        Action::make('reply')
                            ->label('Reply by email')
                            ->icon(Heroicon::OutlinedArrowUturnLeft)
                            ->url('mailto:'.$record->email.'?subject='.rawurlencode('Re: '.$record->subject)),
                    ])
                    ->mutateRecordDataUsing(function (array $data, Inbox $record): array {
                        $record->markAsRead();

                        return $data;
                    }),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkAction::make('markRead')
                    ->label('Mark as read')
                    ->icon(Heroicon::OutlinedEnvelopeOpen)
                    ->action(fn (Collection $records) => Inbox::whereKey($records->modelKeys())->update(['read_at' => now()]))
                    ->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInboxes::route('/'),
        ];
    }
}
