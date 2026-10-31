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
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('label')
                    ->label('Route name')
                    ->helperText('The front-end route this page links to, e.g. front.news. Active pages appear in the site menu.')
                    ->required()
                    ->maxLength(255),
                Toggle::make('active')->label('Show in menu')->default(true)->inline(false),
            ]),
            Fields::seo(static::$disk)->collapsed(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('label')->label('Route name')->searchable()->fontFamily('mono'),
                TextColumn::make('contents_count')->counts('contents')->label('Content blocks')->badge()->color('gray'),
                ToggleColumn::make('active')->label('In menu'),
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
