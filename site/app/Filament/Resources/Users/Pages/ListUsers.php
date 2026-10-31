<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\InviteUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label('Invite user')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('gray')
                ->modalDescription('They will receive an email with a link to choose their name and password.')
                ->schema([
                    TextInput::make('email')->email()->required()->unique('users', 'email'),
                    Select::make('role_id')->label('Role')->options(fn () => Role::pluck('label', 'id')),
                ])
                ->action(function (array $data, InviteUser $invite) {
                    $invite($data['email'], $data['role_id'] ?? null);

                    Notification::make()->title('Invitation sent')->success()->send();
                }),
            CreateAction::make(),
        ];
    }
}
