<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Meat;
use App\Models\PackType;
use App\Models\Product;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Everything on the menu: one flavour of one meat in a pack type (Texas BBQ Beef,
 * 100 g pack), or a mix (the taster box).
 */
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    /** @return array<int, string> */
    protected static function packTypeOptions(): array
    {
        return PackType::with('text')->orderBy('order_column')->get()->mapWithKeys(fn (PackType $t) => [$t->id => $t->name()])->all();
    }

    /** @return array<int, string> */
    protected static function meatOptions(): array
    {
        return Meat::with('text')->orderBy('order_column')->get()->mapWithKeys(fn (Meat $m) => [$m->id => $m->name()])->all();
    }

    public static function form(Schema $schema): Schema
    {
        $currency = config('shop.currency');
        $isMix = fn (Get $get) => (bool) PackType::find($get('pack_type_id'))?->mix;

        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255)
                    ->helperText('For one flavour, just the flavour ("Texas BBQ"). The site adds the meat.'),
                TextInput::make('tagline')->maxLength(255)->helperText('One short line, e.g. "Smoky, sweet, Texas-sized."'),
                Textarea::make('notes')->rows(3)->helperText('A sentence or two about the taste.'),
            ]),
            Section::make('Product')->columns(2)->columnSpanFull()->schema([
                Select::make('pack_type_id')->label('Pack type')
                    ->options(fn () => static::packTypeOptions())
                    ->required()->live()->native(false)
                    ->helperText('Add more under Shop → Pack types.'),
                Select::make('meat_id')->label('Meat')
                    ->options(fn () => static::meatOptions())
                    ->required(fn (Get $get) => ! $isMix($get))
                    ->hidden($isMix)->native(false)
                    ->helperText('Add more under Shop → Meat types.'),
                TextInput::make('price')->numeric()->integer()->minValue(0)->required()->suffix($currency)
                    ->helperText('The price of one of these, as sold.'),
                TextInput::make('grams')->label('Weight')->numeric()->integer()->minValue(1)->required()->suffix('g')->default(100),
                Select::make('heat')->options(array_combine(range(1, 5), range(1, 5)))->default(1)->required()
                    ->hidden($isMix)->native(false)->helperText('1 = no heat, 5 = very hot.'),
                Toggle::make('active')->label('On the website')->default(true)->columnSpanFull(),
            ]),
            Section::make('Colours')->description('Used on the drawing and around the product on the website.')
                ->columnSpanFull()->schema([
                    Grid::make(3)->schema([
                        ColorPicker::make('band')->label('Label band')->required()->default('#b8261c'),
                        ColorPicker::make('band_text')->label('Text on the band')->required()->default('#ffffff'),
                        ColorPicker::make('accent')->label('Glow / accent')->required()->default('#e0261f'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $currency = config('shop.currency');

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['text', 'packType.text', 'meat.text']))
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->columns([
                ColorColumn::make('band')->label(''),
                Translations::column('name')->weight('medium')
                    ->description(fn (Product $record) => $record->translation()?->tagline),
                TextColumn::make('meat_id')->label('Meat')->badge()
                    ->state(fn (Product $record) => $record->isMix() ? 'Mix' : $record->meat?->name())
                    ->color(fn (Product $record) => $record->isMix() ? 'info' : 'warning'),
                TextColumn::make('price')->state(fn (Product $record) => "{$record->price} {$currency}")
                    ->description(fn (Product $record) => $record->packType?->name()." · {$record->grams} g"),
                ToggleColumn::make('active')->label('On the website'),
            ])
            ->filters([
                SelectFilter::make('meat_id')->label('Meat')->options(fn () => static::meatOptions()),
                SelectFilter::make('pack_type_id')->label('Pack type')->options(fn () => static::packTypeOptions()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
