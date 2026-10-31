<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Support\Resource;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('role_id')->label('Role')->relationship('role', 'label')->preload(),
                Toggle::make('active')
                    ->default(true)
                    ->inline(false)
                    ->helperText('Inactive users cannot sign in.')
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->rule('min:8')
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave empty to keep the current password.' : null),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['role', 'invitation']))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium')
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('role.label')->label('Role')->badge(),
                IconColumn::make('invitation')
                    ->label('Invite pending')
                    ->state(fn (User $record) => $record->invitation !== null)
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedEnvelope)
                    ->falseIcon(null)
                    ->trueColor('warning'),
                ToggleColumn::make('active')
                    ->disabled(fn (User $record) => $record->is(auth()->user())),
                TextColumn::make('created_at')->label('Joined')->date()->sortable()->toggleable()->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('role')->relationship('role', 'label'),
                TernaryFilter::make('active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $record) => $record->is(auth()->user())),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
