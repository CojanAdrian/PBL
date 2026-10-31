<?php

namespace App\Filament\Resources\Policies;

use App\Filament\Resources\Policies\Pages\CreatePolicy;
use App\Filament\Resources\Policies\Pages\EditPolicy;
use App\Filament\Resources\Policies\Pages\ListPolicies;
use App\Filament\Support\Resource;
use App\Filament\Support\Schemas\ArticleSchema;
use App\Models\Policy;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PolicyResource extends Resource
{
    protected static ?string $model = Policy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Legal & Help';

    protected static ?string $navigationLabel = 'Privacy policy';

    protected static ?string $modelLabel = 'policy section';

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
            'index' => ListPolicies::route('/'),
            'create' => CreatePolicy::route('/create'),
            'edit' => EditPolicy::route('/{record}/edit'),
        ];
    }
}
