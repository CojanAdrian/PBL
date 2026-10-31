<?php

namespace App\Http\Controllers;

use App\Models\InviteUser;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = InviteUser::with('user')->where('token', $token)->firstOrFail();

        return view('auth.invitation', ['invitation' => $invitation]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = InviteUser::with('user')->where('token', $token)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($invitation, $data) {
            $invitation->user->update($data + ['email_verified_at' => now()]);
            $invitation->delete();

            return $invitation->user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(Filament::getPanel('admin')->getUrl());
    }
}
