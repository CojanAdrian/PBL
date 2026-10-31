<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
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
 * The jerky on sale: packs of one flavour (beef or chicken) and boxes.
 */
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $currency = config('shop.currency');
        $popular = config('shop.popular_size');
        $isBox = fn (Get $get) => $get('type') === Product::BOX;

        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255)
                    ->helperText('For packs, just the flavour ("Texas BBQ"). The site adds Beef or Chicken.'),
                TextInput::make('tagline')->maxLength(255)->helperText('One short line, e.g. "Smoky, sweet, Texas-sized."'),
                Textarea::make('notes')->rows(3)->helperText('A sentence or two about the taste.'),
            ]),
            Section::make('Product')->columns(2)->columnSpanFull()->schema([
                Select::make('type')
                    ->options([Product::PACK => 'Pack (one flavour)', Product::BOX => 'Box (several flavours)'])
                    ->default(Product::PACK)->required()->live()->native(false),
                Select::make('meat')
                    ->options(collect(config('shop.meats'))->map(fn ($meat) => $meat['name'])->all())
                    ->required(fn (Get $get) => ! $isBox($get))
                    ->hidden($isBox)->native(false),
                TextInput::make('price')->numeric()->integer()->minValue(0)->required()->suffix($currency)
                    ->helperText(fn (Get $get) => $isBox($get) ? 'The price of the whole box.' : "The price of one {$popular} g pack."),
                TextInput::make('grams')->label('Box weight')->numeric()->integer()->minValue(1)->suffix('g')
                    ->required($isBox)->visible($isBox),
                Select::make('heat')->options(array_combine(range(1, 5), range(1, 5)))->default(1)->required()
                    ->hidden($isBox)->native(false)->helperText('1 = no heat, 5 = very hot.'),
                TextInput::make('key')->label('Code')
                    ->required()->maxLength(64)->unique(ignoreRecord: true)
                    ->regex('/^[a-z0-9]+(-[a-z0-9]+)*$/')
                    ->disabledOn('edit')
                    ->helperText('Lowercase with dashes, e.g. beef-texas-bbq. Used by the website and saved in orders, so it can\'t change later.'),
                Toggle::make('active')->label('On the website')->default(true)->columnSpanFull(),
            ]),
            Section::make('Colours')->description('Used on the pouch drawing and around the product on the website.')
                ->columnSpanFull()->schema([
                    Grid::make(3)->schema([
                        ColorPicker::make('band')->label('Pouch band')->required()->default('#b8261c'),
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
            ->modifyQueryUsing(fn ($query) => $query->with('text'))
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->columns([
                ColorColumn::make('band')->label(''),
                Translations::column('name')->weight('medium')
                    ->description(fn (Product $record) => $record->translation()?->tagline),
                TextColumn::make('meat')->badge()
                    ->state(fn (Product $record) => $record->isBox() ? 'Box' : config("shop.meats.{$record->meat}.name", $record->meat))
                    ->color(fn (Product $record) => match (true) {
                        $record->isBox() => 'info',
                        $record->meat === 'chicken' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('price')->state(fn (Product $record) => "{$record->price} {$currency}")
                    ->description(fn (Product $record) => $record->isBox() ? "{$record->grams} g box" : config('shop.popular_size').' g pack'),
                ToggleColumn::make('active')->label('On the website'),
            ])
            ->filters([
                SelectFilter::make('meat')->options(collect(config('shop.meats'))->map(fn ($meat) => $meat['name'])->all()),
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
