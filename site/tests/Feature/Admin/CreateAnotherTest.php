<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Terms\Pages\CreateTerm;
use App\Filament\Resources\Terms\TermResource;
use App\Models\Terms;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class CreateAnotherTest extends TestCase
{
    public function test_create_another_keeps_the_first_record(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateTerm::class)
            ->fillForm([
                'trans' => [
                    'en' => ['title' => 'First', 'text' => '<p>One</p>'],
                    'ro' => ['title' => 'Primul', 'text' => '<p>Unu</p>'],
                ],
            ])
            ->call('create', true)
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $this->assertSame(1, Terms::count());
        $this->assertSame('First', Terms::with('text')->sole()->translation('en')->title);
    }

    public function test_cancel_opens_the_list_instead_of_going_back_in_history(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(TermResource::getUrl('create'))
            ->assertOk()
            ->assertSee('href="'.TermResource::getUrl('index').'"', escape: false)
            ->assertDontSee('window.history.back()');
    }

    public function test_missing_languages_are_reported_and_nothing_is_saved(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateTerm::class)
            ->fillForm(['trans' => ['en' => ['title' => 'First', 'text' => '<p>One</p>']]])
            ->call('create', true)
            ->assertHasFormErrors(['trans.ro.title', 'trans.ro.text'])
            ->assertNotified('Not saved yet');

        $this->assertSame(0, Terms::count());
    }
}
