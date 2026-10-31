<?php

namespace App\Filament\Resources\Images;

use App\Filament\Resources\Images\Pages\CreateImage;
use App\Filament\Resources\Images\Pages\EditImage;
use App\Filament\Resources\Images\Pages\ListImages;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Models\Image;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;

class ImageResource extends Resource
{
    /** Every image is used by the site, so image keys can't be deleted (only replaced). */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    protected static ?string $model = Image::class;

    protected static ?string $disk = 'image';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->helperText('Used in Blade as $images[\'key\']'),
                Select::make('page_id')->label('Page')->relationship('page', 'label')->searchable()->preload(),
                Fields::image('img', static::$disk)->label('Image')->required()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('page'))
            ->defaultGroup('page.label')
            ->columns([
                Fields::imageColumn('img', static::$disk)->imageSize(56),
                TextColumn::make('key')->searchable()->sortable()->fontFamily('mono')->copyable(),
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
            'index' => ListImages::route('/'),
            'create' => CreateImage::route('/create'),
            'edit' => EditImage::route('/{record}/edit'),
        ];
    }
}
