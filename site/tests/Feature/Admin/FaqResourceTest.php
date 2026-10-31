<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Models\Faq;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class FaqResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_it_creates_a_faq_with_translations(): void
    {
        Livewire::test(CreateFaq::class)
            ->fillForm([
                'active' => true,
                'trans' => [
                    'en' => ['title' => 'Question', 'text' => '<p>Answer</p>'],
                    'ro' => ['title' => 'Întrebare', 'text' => '<p>Răspuns</p>'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $faq = Faq::with('text')->sole();
        $this->assertSame('Question', $faq->translation('en')->title);
        $this->assertSame('Întrebare', $faq->translation('ro')->title);
    }

    public function test_every_active_language_is_required(): void
    {
        Livewire::test(CreateFaq::class)
            ->fillForm([
                'trans' => [
                    'en' => ['title' => 'Question', 'text' => 'Answer'],
                    'ro' => ['title' => '', 'text' => ''],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['trans.ro.title' => 'required', 'trans.ro.text'])
            ->assertHasNoFormErrors(['trans.en.title', 'trans.en.text']);

        $this->assertSame(0, Faq::count());
    }

    public function test_creating_returns_to_the_list(): void
    {
        Livewire::test(CreateFaq::class)
            ->fillForm([
                'trans' => [
                    'en' => ['title' => 'Question', 'text' => 'Answer'],
                    'ro' => ['title' => 'Întrebare', 'text' => 'Răspuns'],
                ],
            ])
            ->call('create')
            ->assertRedirect(FaqResource::getUrl('index'));
    }

    public function test_it_updates_translations_and_returns_to_the_list(): void
    {
        $faq = Faq::create(['active' => true]);
        $faq->text()->create(['lang' => 'en', 'title' => 'Old', 'text' => 'Old']);
        $faq->text()->create(['lang' => 'ro', 'title' => 'Vechi', 'text' => 'Vechi']);

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->assertSchemaStateSet(['trans.en.title' => 'Old', 'trans.ro.title' => 'Vechi'])
            ->fillForm([
                'trans' => [
                    'en' => ['title' => 'New', 'text' => 'New'],
                    'ro' => ['title' => 'Nou', 'text' => 'Nou'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(FaqResource::getUrl('index'));

        $faq->refresh()->load('text');
        $this->assertSame('New', $faq->translation('en')->title);
        $this->assertSame('Nou', $faq->translation('ro')->title);
    }

    public function test_a_language_cannot_be_emptied_on_edit(): void
    {
        $faq = Faq::create(['active' => true]);
        $faq->text()->create(['lang' => 'en', 'title' => 'Old', 'text' => 'Old']);
        $faq->text()->create(['lang' => 'ro', 'title' => 'Vechi', 'text' => 'Vechi']);

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->fillForm(['trans' => ['ro' => ['title' => '', 'text' => '']]])
            ->call('save')
            ->assertHasFormErrors(['trans.ro.title', 'trans.ro.text']);

        $this->assertSame('Vechi', $faq->refresh()->load('text')->translation('ro')->title);
    }
}
