<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Service;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
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

class ServiceResource extends Resource
{
    /** Not used by the Big Ed's site, so it stays out of the menu (the section still works). */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Service::class;

    protected static ?string $disk = 'service';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('title')->required($required)->maxLength(255),
                TextInput::make('sub_title')->label('Subtitle')->maxLength(255),
                RichEditor::make('text')->label('Description'),
            ]),
            Section::make('Media')->columns(2)->columnSpanFull()->schema([
                Fields::image('img', static::$disk)->label('Main image')->required(),
                Toggle::make('active')->label('Published')->default(true),
                Fields::gallery(static::$disk),
            ]),
            Fields::seo(static::$disk),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text')->withCount('gallery'))
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->columns([
                Fields::imageColumn('img', static::$disk),
                Translations::column('title')->weight('medium')
                    ->description(fn (Service $record) => $record->translation()?->sub_title),
                TextColumn::make('gallery_count')->label('Photos')->badge()->color('gray'),
                ToggleColumn::make('active')->label('Published'),
                TextColumn::make('updated_at')->since()->sortable()->toggleable()->visibleFrom('md'),
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
        return $record->translation()?->title ?? "Service #{$record->getKey()}";
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
