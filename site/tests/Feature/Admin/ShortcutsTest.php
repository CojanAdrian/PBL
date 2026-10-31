<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Inboxes\InboxResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Support\Shortcuts;
use App\Filament\Widgets\WebsiteCard;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ShortcutsTest extends TestCase
{
    public function test_users_get_the_default_shortcuts(): void
    {
        $this->actingAs($admin = User::factory()->admin()->create());

        $this->assertSame(
            ['Orders', 'Products', 'Edit site', 'Invite'],
            array_column(Shortcuts::resolve($admin), 'label'),
        );
    }

    public function test_a_user_can_save_their_own_shortcuts(): void
    {
        $this->actingAs($admin = User::factory()->admin()->create());

        Livewire::test(WebsiteCard::class)
            ->callAction('editShortcuts', data: [
                'shortcuts' => [
                    ['label' => 'FAQ', 'icon' => 'question-mark-circle', 'target' => FaqResource::class.'@create', 'url' => null],
                    ['label' => 'Shop', 'icon' => 'shopping-bag', 'target' => 'custom', 'url' => 'https://shop.example.com'],
                ],
            ])
            ->assertHasNoErrors()
            ->assertNotified('Shortcuts saved');

        $shortcuts = Shortcuts::resolve($admin->refresh());

        $this->assertSame(['FAQ', 'Shop'], array_column($shortcuts, 'label'));
        $this->assertSame(FaqResource::getUrl('create'), $shortcuts[0]['url']);
        $this->assertTrue($shortcuts[1]['external']);

        Livewire::test(WebsiteCard::class)->assertSee('FAQ')->assertSee('Shop')->assertDontSee('Invite');
    }

    public function test_a_custom_link_needs_a_url(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(WebsiteCard::class)
            ->callAction('editShortcuts', data: [
                'shortcuts' => [['label' => 'Shop', 'icon' => 'shopping-bag', 'target' => 'custom', 'url' => '']],
            ])
            ->assertHasActionErrors();
    }

    public function test_shortcuts_to_pages_a_user_cannot_open_are_hidden(): void
    {
        $this->actingAs($editor = User::factory()->create());

        $editor->forceFill(['shortcuts' => [
            ['label' => 'Inbox', 'icon' => 'inbox', 'target' => InboxResource::class.'@index', 'url' => null],
            ['label' => 'News', 'icon' => 'newspaper', 'target' => NewsResource::class.'@create', 'url' => null],
        ]])->save();

        $this->assertSame(['Inbox'], array_column(Shortcuts::resolve($editor), 'label'));
        $this->assertArrayNotHasKey(NewsResource::class.'@index', Shortcuts::destinations());
    }

    public function test_reset_restores_the_defaults(): void
    {
        $this->actingAs($admin = User::factory()->admin()->create());
        $admin->forceFill(['shortcuts' => []])->save();

        Livewire::test(WebsiteCard::class)
            ->callAction('editShortcuts', arguments: ['reset' => true]);

        $this->assertNull($admin->refresh()->shortcuts);
    }
}
