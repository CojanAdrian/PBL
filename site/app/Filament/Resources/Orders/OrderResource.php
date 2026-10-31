<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Filament\Support\Resource;
use App\Models\Order;
use App\Shop\Notifier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;
use UnitEnum;

/**
 * Preorders from the website. Their status is kept in the Google Sheet, so
 * here they are read-only; deleting one also removes its row from the sheet.
 */
class OrderResource extends Resource
{
    /** Every active user can see the orders. */
    protected static bool $adminOnly = false;

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('created_at', '>=', now()->startOfDay())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Orders today';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name', 'phone', 'email'];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(3)->columnSpanFull()->schema([
                TextEntry::make('name'),
                TextEntry::make('phone')->copyable()->url(fn (Order $record) => "tel:{$record->phone}"),
                TextEntry::make('email')->placeholder('—')->copyable()->url(fn (Order $record) => $record->email ? "mailto:{$record->email}" : null),
                TextEntry::make('created_at')->label('Placed')->dateTime('j M Y, H:i'),
                TextEntry::make('wanted_date')->label('Wanted date')->date('j M Y')->placeholder('—'),
                TextEntry::make('locale')->label('Site language')->state(fn (Order $record) => strtoupper((string) $record->locale))->placeholder('—'),
            ]),
            Section::make(fn (Order $record) => "{$record->total_price} {$record->currency}")
                ->description(fn (Order $record) => "{$record->packs()} ".($record->packs() === 1 ? 'pack' : 'packs').", {$record->total_grams} g, delivery")
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('items')->hiddenLabel()
                        ->state(fn (Order $record) => array_map(
                            fn (array $item) => Order::describeItem($item).': '.($item['price'] * $item['qty'])." {$record->currency}",
                            $record->items ?? [],
                        ))
                        ->listWithLineBreaks()->bulleted(),
                    TextEntry::make('note')->placeholder('No note')->prose(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->copyable()->weight('bold')
                    ->description(fn (Order $record) => $record->created_at?->format('j M, H:i')),
                TextColumn::make('name')->searchable()
                    ->description(fn (Order $record) => $record->phone),
                TextColumn::make('items')->label('Order')
                    ->state(fn (Order $record) => implode("\n", $record->lines()))
                    ->formatStateUsing(fn (string $state) => nl2br(e($state)))->html()
                    ->wrap(),
                TextColumn::make('total_price')->label('Total')->sortable()
                    ->state(fn (Order $record) => "{$record->total_price} {$record->currency}")
                    ->description(fn (Order $record) => "{$record->total_grams} g"),
                TextColumn::make('wanted_date')->label('Wanted')->date('j M')->placeholder('—')->toggleable()->visibleFrom('md'),
                TextColumn::make('created_at')->label('Placed')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('today')->label('Today')
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->startOfDay())),
                Filter::make('week')->label('Last 7 days')
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(6)->startOfDay())),
            ])
            ->recordActions([
                ViewAction::make()->modalHeading(fn (Order $record) => "Order {$record->code}")
                    ->modalFooterActions(fn (Order $record) => [
                        Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->url("tel:{$record->phone}"),
                    ]),
                DeleteAction::make()->iconButton()
                    ->modalDescription('It is removed from here and from the Google Sheet, and can\'t be brought back.')
                    ->after(fn (Order $record) => static::removeFromSheet([$record->code])),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->modalDescription('They are removed from here and from the Google Sheet, and can\'t be brought back.')
                    ->after(fn (Collection $records) => static::removeFromSheet($records->pluck('code')->all())),
            ]);
    }

    /** Deletes the orders' rows from the Google Sheet, and says so when that fails. */
    public static function removeFromSheet(array $codes): void
    {
        $notifier = app(Notifier::class);
        if (! $notifier->sheetEnabled() || ! $codes) {
            return;
        }
        try {
            $notifier->sheetRemove($codes);
        } catch (Throwable $e) {
            Notification::make()->warning()
                ->title('Not removed from the Google Sheet')
                ->body('Delete the row there by hand. '.$e->getMessage())
                ->persistent()
                ->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}
