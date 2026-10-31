<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Filament\Support\Resource;
use App\Models\Role;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->required()->maxLength(255),
            TextInput::make('role')
                ->label('Key')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText('The "admin" key grants full access.')
                ->disabled(fn (?Role $record) => $record?->role === Role::ADMIN),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->weight('medium')->searchable(),
                TextColumn::make('role')->label('Key')->fontFamily('mono'),
                TextColumn::make('users_count')->counts('users')->label('Users')->badge()->color('gray'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (Role $record) => $record->role === Role::ADMIN),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
