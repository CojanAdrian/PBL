<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;

class Fields
{
    /**
     * Image upload stored as a bare filename on a per-entity disk, with an in-browser crop/resize editor.
     */
    public static function image(string $name, string $disk): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->imageEditor()
            ->disk($disk)
            ->visibility('public')
            ->maxSize(5 * 1024);
    }

    public static function gallery(string $disk): FileUpload
    {
        return static::image('gallery', $disk)
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->panelLayout('grid')
            ->columnSpanFull();
    }

    /**
     * Optional SEO block bound to the record's `meta()` relation (image + per-language title/description/keywords).
     */
    public static function seo(string $disk): Section
    {
        return Section::make('SEO')
            ->description('Search engine and social sharing details. Leave empty to use defaults.')
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->collapsible()
            ->collapsed()
            ->columnSpanFull()
            ->schema([
                Translations::tabs(fn (bool|Closure $required) => [
                    TextInput::make('title')->required($required)->maxLength(255),
                    Textarea::make('text')->label('Description')->required($required)->rows(2)->maxLength(500),
                    TextInput::make('keywords')->required($required)->placeholder('comma, separated, keywords'),
                ], statePath: 'meta.trans', requireAll: false),
                static::image('meta.img', $disk)->label('Share image'),
            ]);
    }

    public static function imageColumn(string $name, string $disk): ImageColumn
    {
        return ImageColumn::make($name)
            ->disk($disk)
            ->label('')
            ->imageSize(40)
            ->defaultImageUrl(asset('images/no_img.png'));
    }
}
