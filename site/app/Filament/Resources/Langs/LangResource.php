<?php

namespace App\Filament\Resources\Langs;

use App\Filament\Resources\Langs\Pages\ManageLangs;
use App\Filament\Support\Resource;
use App\Models\Lang;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class LangResource extends Resource
{
    /** The site is built for English, Romanian and Russian: languages can't be added from the admin. */
    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $model = Lang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Languages';

    protected static ?string $modelLabel = 'language';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255)->placeholder('English'),
            TextInput::make('code')
                ->required()
                ->maxLength(10)
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->placeholder('en')
                ->helperText('Used in URLs (/en/…) and as the translation tab key.')
                ->disabledOn('edit'),
            Toggle::make('active')
                ->default(true)
                ->helperText('Active languages get a tab in every translated form and a public URL prefix.')
                ->disabled(fn (?Lang $record) => $record?->code === config('app.locale')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->weight('medium')->searchable()
                    ->description(fn (Lang $record) => $record->code === config('app.locale') ? 'Default language' : null),
                TextColumn::make('code')->badge()->color('gray'),
                ToggleColumn::make('active')
                    ->disabled(fn (Lang $record) => $record->code === config('app.locale')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Lang $record) => $record->code === config('app.locale'))
                    ->modalDescription('All content translated into this language will be deleted.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLangs::route('/'),
        ];
    }
}
