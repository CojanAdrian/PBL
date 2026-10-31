<?php

namespace Tests\Feature;

use App\Shop\Installer;
use Tests\TestCase;

class FrontSiteTest extends TestCase
{
    public function test_root_redirects_to_the_default_locale(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_the_jerky_page_renders_in_each_active_language(): void
    {
        app(Installer::class)->run();

        $this->get('/en')->assertOk()->assertSee('Real meat.')->assertSee('id="menu-data"', false)->assertSee('Texas BBQ');
        $this->get('/ro')->assertOk()->assertSee('Carne adevărată.')->assertSee('Teriyaki cu usturoi');
    }

    public function test_the_page_lists_every_active_language(): void
    {
        $this->get('/en')->assertOk()
            ->assertSee('hreflang="ro"', false)
            ->assertSee('href="http://localhost/ro"', false);
    }

    public function test_unknown_locale_redirects_home(): void
    {
        $this->get('/xx')->assertRedirect('/');
    }

    public function test_the_starters_other_pages_are_switched_off(): void
    {
        foreach (['/faq', '/services', '/news', '/contact', '/terms', '/privacy-policy'] as $path) {
            $this->get("/en{$path}")->assertNotFound();
        }
    }
}
