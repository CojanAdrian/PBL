<?php

namespace App\Filament\Resources\Socials;

use App\Filament\Resources\Socials\Pages\CreateSocial;
use App\Filament\Resources\Socials\Pages\EditSocial;
use App\Filament\Resources\Socials\Pages\ListSocials;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Social;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
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

class SocialResource extends Resource
{
    /** Not used by the Big Ed's site, so it stays out of the menu (the section still works). */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Social::class;

    protected static ?string $disk = 'social';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Social links';

    protected static ?string $modelLabel = 'social link';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('url')->label('URL')->url()->required()->maxLength(255),
                Toggle::make('active')->default(true)->inline(false),
                Fields::image('img', static::$disk)->label('Icon')->required(),
            ]),
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text'))
            ->columns([
                Fields::imageColumn('img', static::$disk),
                Translations::column('name')->weight('medium'),
                TextColumn::make('url')->label('URL')->url(fn (Social $record) => $record->url, true)->color('primary')->limit(40),
                ToggleColumn::make('active'),
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
            'index' => ListSocials::route('/'),
            'create' => CreateSocial::route('/create'),
            'edit' => EditSocial::route('/{record}/edit'),
        ];
    }
}
