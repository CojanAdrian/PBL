<?php

namespace Tests\Feature;

use App\Filament\Pages\VisualEditor;
use App\Models\Content;
use App\Models\Image;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisualEditorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['label' => 'front.index', 'active' => true]);
    }

    public function test_texts_are_created_on_first_use_with_their_default(): void
    {
        $this->get('/en')->assertOk()->assertSee('Real meat.');

        $content = Content::with('text')->where('key', 'hero_title_1')->sole();
        $this->assertSame(Page::where('label', 'front.index')->value('id'), $content->page_id);
        $this->assertSame(['en' => 'Real meat.'], $content->text->pluck('text', 'lang')->all());
    }

    public function test_untranslated_texts_fall_back_to_the_main_language(): void
    {
        $this->get('/en');
        Content::where('key', 'hero_title_1')->sole()->text()->update(['text' => 'Edited title']);

        $this->get('/ro')->assertOk()->assertSee('Edited title');
    }

    public function test_edit_hooks_only_appear_for_admins_in_the_editor(): void
    {
        $this->get('/en?cms_edit=1')->assertDontSee('data-cms-text', false)->assertDontSee('cms-badge', false);

        $this->actingAs(User::factory()->create())
            ->get('/en?cms_edit=1')->assertDontSee('data-cms-text', false);

        $this->actingAs(User::factory()->admin()->create());
        $this->get('/en')->assertDontSee('data-cms-text', false);

        $this->get('/en?cms_edit=1')
            ->assertSee('data-cms-text="hero_title_1"', false)
            ->assertSee('data-cms-image="hero_logo"', false)
            ->assertSee('cms-badge', false);
    }

    public function test_admins_can_save_a_text_in_a_language(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->postJson('/cms/text', ['key' => 'hero_title_1', 'locale' => 'ro', 'text' => 'Titlu nou'])
            ->assertOk();

        $this->assertSame('Titlu nou', Content::where('key', 'hero_title_1')->sole()->translation('ro')->text);
        $this->get('/ro')->assertSee('Titlu nou');
    }

    public function test_plain_text_never_carries_editor_markup(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/en?cms_edit=1');

        $this->assertSame('Our team', app(\App\Support\Cms\Cms::class)->plain('hero_alt', 'Our team'));
        $this->assertStringNotContainsString('data-cms', app(\App\Support\Cms\Cms::class)->plain('hero_alt'));
    }

    public function test_line_breaks_are_kept(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->postJson('/cms/text', ['key' => 'hero_title_1', 'locale' => 'en', 'text' => "First line\r\nSecond <line>"])
            ->assertOk();

        $this->assertSame("First line\nSecond <line>", Content::where('key', 'hero_title_1')->sole()->translation('en')->text);
        $this->get('/en')->assertSee('First line<br>Second &lt;line&gt;', false);
    }

    public function test_admins_can_replace_an_image(): void
    {
        Storage::fake('image');
        $this->actingAs(User::factory()->admin()->create());

        $first = $this->post('/cms/image', ['key' => 'hero_logo', 'image' => UploadedFile::fake()->create('a.jpg', 10, 'image/jpeg')], ['Accept' => 'application/json'])
            ->assertOk()->json('url');
        $old = Image::where('key', 'hero_logo')->sole()->img;

        $this->post('/cms/image', ['key' => 'hero_logo', 'image' => UploadedFile::fake()->create('b.jpg', 10, 'image/jpeg')], ['Accept' => 'application/json'])
            ->assertOk();
        $new = Image::where('key', 'hero_logo')->sole()->img;

        $this->assertNotSame($old, $new);
        Storage::disk('image')->assertMissing($old);
        Storage::disk('image')->assertExists($new);
        $this->assertStringContainsString($old, $first);
        $this->get('/en')->assertSee(Storage::disk('image')->url($new));
    }

    public function test_only_admins_can_save(): void
    {
        $this->postJson('/cms/text', ['key' => 'hero_title_1', 'locale' => 'en', 'text' => 'x'])->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->postJson('/cms/text', ['key' => 'hero_title_1', 'locale' => 'en', 'text' => 'x'])->assertForbidden();

        $this->assertSame(0, Content::count());
    }

    public function test_the_editor_page_is_for_admins(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(VisualEditor::getUrl())->assertOk()->assertSee('Visual editor');

        $this->actingAs(User::factory()->create())
            ->get(VisualEditor::getUrl())->assertForbidden();
    }
}
