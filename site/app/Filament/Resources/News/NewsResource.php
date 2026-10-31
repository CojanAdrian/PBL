<?php

namespace App\Filament\Resources\News;

use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\News;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $disk = 'news';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'article';

    protected static ?string $pluralModelLabel = 'news';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('title')->required($required)->maxLength(255),
                TextInput::make('sub_title')->label('Subtitle')->required($required)->maxLength(255),
                Textarea::make('excerpt')->required($required)->rows(2)
                    ->helperText('Short summary shown in news listings.'),
                RichEditor::make('text')->label('Article')->required($required),
            ]),
            Section::make('Media')->columns(2)->columnSpanFull()->schema([
                Fields::image('img', static::$disk)->label('Thumbnail')->required(),
                Fields::image('cover_img', static::$disk)->label('Cover image')->required(),
                Toggle::make('active')->label('Published')->default(false),
                Fields::gallery(static::$disk),
            ]),
            Fields::seo(static::$disk),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Fields::imageColumn('img', static::$disk),
                Translations::column('title')->weight('medium')->wrap()
                    ->description(fn (News $record) => $record->translation()?->sub_title),
                ToggleColumn::make('active')->label('Published'),
                TextColumn::make('created_at')->label('Created')->date()->sortable()->visibleFrom('md'),
            ])
            ->filters([
                TernaryFilter::make('active')->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['text.title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->translation()?->title ?? "Article #{$record->getKey()}";
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }
}
