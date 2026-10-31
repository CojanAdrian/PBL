<?php

namespace App\Filament\Resources\Meats;

use App\Filament\Resources\Meats\Pages\CreateMeat;
use App\Filament\Resources\Meats\Pages\EditMeat;
use App\Filament\Resources\Meats\Pages\ListMeats;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Meat;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Meat types: beef, chicken, … A product of one flavour belongs to one meat.
 */
class MeatResource extends Resource
{
    protected static ?string $model = Meat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Meat types';

    protected static ?string $modelLabel = 'meat type';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255)->helperText('e.g. Beef. Shown after the flavour: "Texas BBQ Beef".'),
            ]),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('key')->label('Code')->required()->maxLength(32)->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9]+(-[a-z0-9]+)*$/')->disabledOn('edit')
                    ->helperText('Lowercase, e.g. pork. Printed on the pouch drawing ("PREMIUM PORK JERKY").'),
                Toggle::make('active')->label('On the website')->default(true)->inline(false)
                    ->helperText('Switched off, all its products are hidden.'),
            ]),
            Section::make('Pouch colours')->description('The colour of this meat\'s pouches on the website.')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    ColorPicker::make('pouch_top')->label('Top')->required()->default('#2a2624'),
                    ColorPicker::make('pouch_bottom')->label('Bottom')->required()->default('#0d0b0a'),
                    ColorPicker::make('pouch_cap')->label('Seal strip')->required()->default('#0a0908'),
                ]),
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
                ColorColumn::make('pouch_top')->label(''),
                Translations::column('name')->weight('medium'),
                TextColumn::make('products_count')->label('Products'),
                ToggleColumn::make('active')->label('On the website'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->iconButton()
                    ->hidden(fn (Meat $record) => $record->products_count > 0)
                    ->tooltip('Delete (only possible when no product uses it)'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeats::route('/'),
            'create' => CreateMeat::route('/create'),
            'edit' => EditMeat::route('/{record}/edit'),
        ];
    }
}
