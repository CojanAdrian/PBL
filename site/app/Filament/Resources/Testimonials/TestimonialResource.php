<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Filament\Support\Fields;
use App\Filament\Support\Resource;
use App\Filament\Support\Translations;
use App\Models\Testimonial;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class TestimonialResource extends Resource
{
    /** Not used by the Big Ed's site, so it stays out of the menu (the section still works). */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Testimonial::class;

    protected static ?string $disk = 'testimonial';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('name')->required($required)->maxLength(255),
                TextInput::make('function')->label('Position / company')->required($required)->maxLength(255),
                Textarea::make('text')->label('Quote')->required($required)->rows(4),
            ]),
            Section::make()->columns(2)->columnSpanFull()->schema([
                Fields::image('img', static::$disk)->label('Photo')->avatar()->required(),
                Toggle::make('active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                Fields::imageColumn('img', static::$disk)->circular(),
                Translations::column('name')->weight('medium')
                    ->description(fn (Testimonial $record) => $record->translation()?->function),
                ToggleColumn::make('active'),
            ])
            ->filters([
                TernaryFilter::make('active'),
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
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
