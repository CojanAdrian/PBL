<?php

namespace App\Actions;

use App\Mail\InviteUser as InviteUserMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteUser
{
    /**
     * Create a placeholder account and email a one-time link to finish registration.
     */
    public function __invoke(string $email, ?int $roleId = null): User
    {
        $user = DB::transaction(function () use ($email, $roleId) {
            $user = User::create([
                'name' => Str::before($email, '@'),
                'email' => $email,
                'password' => Str::password(32),
                'role_id' => $roleId,
            ]);

            $user->invitation()->create(['token' => Str::random(64)]);

            return $user;
        });

        Mail::to($email)->send(new InviteUserMail(route('invitation', $user->invitation->token)));

        return $user;
    }
}
