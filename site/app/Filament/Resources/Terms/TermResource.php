<?php

namespace App\Filament\Resources\Terms;

use App\Filament\Resources\Terms\Pages\CreateTerm;
use App\Filament\Resources\Terms\Pages\EditTerm;
use App\Filament\Resources\Terms\Pages\ListTerms;
use App\Filament\Support\Resource;
use App\Filament\Support\Schemas\ArticleSchema;
use App\Models\Terms;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TermResource extends Resource
{
    protected static ?string $model = Terms::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Legal & Help';

    protected static ?string $navigationLabel = 'Terms';

    protected static ?string $modelLabel = 'term';

    public static function form(Schema $schema): Schema
    {
        return ArticleSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleSchema::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTerms::route('/'),
            'create' => CreateTerm::route('/create'),
            'edit' => EditTerm::route('/{record}/edit'),
        ];
    }
}
