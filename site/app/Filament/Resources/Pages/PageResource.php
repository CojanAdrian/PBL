<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    /** Big Ed's is one page: new pages can't be added from the admin. */
    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $model = Page::class;

    protected static ?string $disk = 'page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                // Read-only: the site and the Visual editor find the page by this name.
                TextInput::make('label')
                    ->label('Page')
                    ->formatStateUsing(fn (?string $state) => $state === 'front.index' ? 'Home (the whole site)' : $state)
                    ->disabled()
                    ->dehydrated(false),
            ]),
            Fields::seo(static::$disk)->collapsed(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('label')->label('Page')->formatStateUsing(fn (string $state) => $state === 'front.index' ? 'Home (the whole site)' : $state),
                TextColumn::make('contents_count')->counts('contents')->label('Content blocks')->badge()->color('gray'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
