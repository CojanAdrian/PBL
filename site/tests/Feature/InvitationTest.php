<?php

namespace Tests\Feature;

use App\Actions\InviteUser;
use App\Mail\InviteUser as InviteUserMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    public function test_invited_user_can_finish_registration(): void
    {
        Mail::fake();

        $user = app(InviteUser::class)('new@example.com');
        $token = $user->invitation->token;

        Mail::assertSent(InviteUserMail::class, fn ($mail) => $mail->hasTo('new@example.com') && str_contains($mail->url, $token));

        $this->get("/invite/{$token}")->assertOk()->assertSee('new@example.com');

        $this->post("/invite/{$token}", [
            'name' => 'New Person',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect('/admin');

        $user->refresh();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('New Person', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNull($user->invitation);
    }

    public function test_invalid_tokens_are_not_found(): void
    {
        $this->get('/invite/nope')->assertNotFound();
        $this->post('/invite/nope')->assertNotFound();
    }
}
