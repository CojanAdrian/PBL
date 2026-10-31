<?php

namespace App\Filament\Support\Schemas;

use App\Filament\Support\Translations;
use Closure;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Shared form/table for the simple "title + rich text" entities: FAQ, Terms, Policy.
 */
class ArticleSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Translations::tabs(fn (bool|Closure $required) => [
                TextInput::make('title')->required($required)->maxLength(255),
                RichEditor::make('text')->required($required),
            ]),
            Section::make()->schema([
                Toggle::make('active')->default(true),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('text'))
            ->reorderable('order_column')
            ->defaultSort('order_column')
            ->columns([
                Translations::column('title')->weight('medium')->wrap(),
                ToggleColumn::make('active'),
                TextColumn::make('updated_at')->since()->sortable()->toggleable()->visibleFrom('md'),
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
}
