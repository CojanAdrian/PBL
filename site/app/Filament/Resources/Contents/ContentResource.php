<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Resources\Contents\Pages\CreateContent;
use App\Filament\Resources\Contents\Pages\EditContent;
use App\Filament\Resources\Contents\Pages\ListContents;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Content;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class ContentResource extends Resource
{
    /** Every text is used by the site, so text keys can't be deleted (only edited). */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Text blocks';

    protected static ?string $modelLabel = 'text block';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->helperText('Used in Blade as $content[\'key\']->text'),
                Select::make('page_id')->label('Page')->relationship('page', 'label')->searchable()->preload(),
            ]),
            Translations::tabs(fn (bool|Closure $required) => [
                Textarea::make('text')->required($required)->autosize()->rows(3),
                TextInput::make('link')->label('Link (optional)')->maxLength(255),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['text', 'page']))
            ->defaultGroup('page.label')
            ->columns([
                TextColumn::make('key')->searchable()->sortable()->fontFamily('mono')->copyable(),
                Translations::column('text')->limit(80)->wrap(),
            ])
            ->filters([
                SelectFilter::make('page')->relationship('page', 'label'),
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
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'edit' => EditContent::route('/{record}/edit'),
        ];
    }
}
