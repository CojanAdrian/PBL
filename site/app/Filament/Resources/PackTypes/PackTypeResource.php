<?php

namespace App\Filament\Resources\PackTypes;

use App\Filament\Resources\PackTypes\Pages\CreatePackType;
use App\Filament\Resources\PackTypes\Pages\EditPackType;
use App\Filament\Resources\PackTypes\Pages\ListPackTypes;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\PackType;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Pack types: pack, box, sack, … Every product is sold in one of them.
 */
class PackTypeResource extends Resource
{
    protected static ?string $model = PackType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Pack types';

    protected static ?string $modelLabel = 'pack type';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255)->helperText('e.g. Pack, Box, Sack. Shown next to the price.'),
            ]),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('key')->label('Code')->required()->maxLength(32)->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9]+(-[a-z0-9]+)*$/')->disabledOn('edit')
                    ->helperText('Lowercase, e.g. sack.'),
                Select::make('style')->label('Drawn as')->options(PackType::STYLES)->default('pouch')->required()->native(false),
                Toggle::make('mix')->label('Several flavours in one (like the taster box)')->inline(false)
                    ->helperText('Its products have no meat or heat, and show all the flavours on the drawing.'),
                Toggle::make('active')->label('On the website')->default(true)->inline(false)
                    ->helperText('Switched off, all its products are hidden.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text')->withCount('products'))
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->columns([
                Translations::column('name')->weight('medium'),
                TextColumn::make('style')->label('Drawn as')->formatStateUsing(fn (string $state) => PackType::STYLES[$state] ?? $state)->badge(),
                IconColumn::make('mix')->label('Mix')->boolean(),
                TextColumn::make('products_count')->label('Products'),
                ToggleColumn::make('active')->label('On the website'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->iconButton()
                    ->hidden(fn (PackType $record) => $record->products_count > 0)
                    ->tooltip('Delete (only possible when no product uses it)'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackTypes::route('/'),
            'create' => CreatePackType::route('/create'),
            'edit' => EditPackType::route('/{record}/edit'),
        ];
    }
}
