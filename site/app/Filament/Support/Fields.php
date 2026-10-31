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
                    TextInput::make('title')->required($required)->maxLength(255)
                        ->helperText('The browser tab, Google result and shared-link title. About 50–60 characters.'),
                    Textarea::make('text')->label('Description')->required($required)->rows(2)->maxLength(500)
                        ->helperText('The line under the title on Google and in shared links. About 120–155 characters.'),
                    TextInput::make('keywords')->required($required)->placeholder('comma, separated, keywords')
                        ->helperText('Optional. Google ignores keywords; they do no harm.'),
                ], statePath: 'meta.trans', requireAll: false),
                static::image('meta.img', $disk)->label('Share image')
                    ->helperText('Shown when the link is shared (WhatsApp, Facebook, Telegram…). 1200 × 630 px, JPG, under 300 KB: WhatsApp leaves out bigger images.'),
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
