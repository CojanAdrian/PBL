<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Models\Inbox;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FrontSiteTest extends TestCase
{
    public function test_root_redirects_to_the_default_locale(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_public_pages_render_in_each_active_language(): void
    {
        foreach (['en', 'ro'] as $locale) {
            foreach (['', '/faq', '/privacy-policy', '/terms', '/services', '/news', '/contact', '/thank-form'] as $path) {
                $this->get("/{$locale}{$path}")->assertOk();
            }
        }
    }

    public function test_unknown_locale_redirects_home(): void
    {
        $this->get('/xx')->assertRedirect('/');
    }

    public function test_unknown_slugs_are_not_found(): void
    {
        $this->get('/en/single-news/missing')->assertNotFound();
        $this->get('/en/single-services/missing')->assertNotFound();
    }

    public function test_contact_form_stores_message_and_emails_admin(): void
    {
        Mail::fake();
        config(['app.admin_email' => 'owner@example.com']);

        $this->post('/en/storage-contact', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'phone' => '+37360000000',
            'subject' => 'Quote',
            'message' => 'Hello!',
        ])->assertRedirect('/en/thank-form');

        $this->assertDatabaseHas('inboxes', ['email' => 'ana@example.com', 'read_at' => null]);
        Mail::assertSent(ContactFormMail::class, fn ($mail) => $mail->hasTo('owner@example.com'));
    }

    public function test_contact_form_validates_input(): void
    {
        $this->post('/en/storage-contact', ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);

        $this->assertSame(0, Inbox::count());
    }
}
