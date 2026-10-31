<?php

namespace Tests\Feature;

use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\Images\ImageResource;
use App\Filament\Resources\Langs\LangResource;
use App\Filament\Resources\Meats\MeatResource;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Content;
use App\Models\Ingredient;
use App\Models\Meat;
use App\Models\PackType;
use App\Models\Product;
use App\Models\User;
use App\Shop\Installer;
use App\Shop\Menu;
use App\Shop\PreorderValidator;
use Tests\TestCase;

class ShopAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(Installer::class)->run();
    }

    public function test_the_installer_adds_meat_and_pack_types_and_ingredients(): void
    {
        $this->assertSame(['beef', 'chicken'], Meat::orderBy('order_column')->pluck('key')->all());
        $this->assertSame(['pack', 'box'], PackType::orderBy('order_column')->pluck('key')->all());
        $this->assertTrue(PackType::where('key', 'box')->value('mix'));
        $this->assertSame(8, Ingredient::count());
        $this->assertSame('Vită', Meat::where('key', 'beef')->sole()->name('ro'));
    }

    public function test_a_new_meat_and_pack_type_can_be_sold(): void
    {
        $pork = Meat::create(['key' => 'pork']);
        $pork->text()->create(['lang' => 'en', 'name' => 'Pork']);
        $sack = PackType::create(['key' => 'sack', 'style' => 'sack']);
        $sack->text()->create(['lang' => 'en', 'name' => 'Sack']);
        $product = Product::create(['key' => 'pork-texas-bbq-sack', 'pack_type_id' => $sack->id, 'meat_id' => $pork->id, 'price' => 400, 'grams' => 500]);
        $product->text()->create(['lang' => 'en', 'name' => 'Texas BBQ']);

        $menu = collect(Menu::build()['flavours'])->keyBy('id');
        $this->assertSame(['pork', 'sack', 'sack', 500, 'Sack'], [
            $menu['pork-texas-bbq-sack']['meat'], $menu['pork-texas-bbq-sack']['type'], $menu['pork-texas-bbq-sack']['style'],
            $menu['pork-texas-bbq-sack']['grams'], $menu['pork-texas-bbq-sack']['typeName'],
        ]);

        $order = PreorderValidator::validate(['name' => 'Ana', 'phone' => '+37369123456', 'items' => [['flavour' => 'pork-texas-bbq-sack', 'grams' => 500, 'qty' => 2]]])['order'];
        $this->assertSame(800, $order['total_price']);
        $this->assertSame('Texas BBQ Pork', $order['items'][0]['name']);
    }

    public function test_switching_off_a_meat_or_pack_type_hides_its_products(): void
    {
        Meat::where('key', 'chicken')->update(['active' => false]);
        $ids = collect(Menu::build()['flavours'])->pluck('id');
        $this->assertFalse($ids->contains('chicken-texas-bbq'));
        $this->assertTrue($ids->contains('beef-texas-bbq'));

        PackType::where('key', 'box')->update(['active' => false]);
        $this->assertCount(0, Menu::build()['boxes']);
    }

    public function test_ingredients_come_from_the_admin(): void
    {
        $extra = Ingredient::create([]);
        $extra->text()->create(['lang' => 'en', 'name' => 'Black pepper']);
        Ingredient::orderBy('order_column')->first()->update(['active' => false]);

        $this->get('/en')->assertOk()->assertSee('Black pepper')->assertDontSee('Beef or chicken');
    }

    public function test_the_logos_are_editable_images(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/en?cms_edit=1')
            ->assertSee('data-cms-image="site_logo"', false)
            ->assertSee('data-cms-image="story_photo"', false)
            ->assertSee('data-cms-text="hero_word"', false);
    }

    public function test_the_builder_knows_when_it_is_in_the_visual_editor(): void
    {
        $this->assertFalse(Menu::build()['editing']);

        $this->actingAs(User::factory()->admin()->create());
        $this->get('/en?cms_edit=1')->assertSee('"editing":true', false);
    }

    public function test_pages_and_languages_cannot_be_added_and_keys_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->assertFalse(PageResource::canCreate());
        $this->assertFalse(LangResource::canCreate());
        $this->assertFalse(ContentResource::canDelete(Content::first()));
        $this->assertFalse(ContentResource::canDeleteAny());
        $this->assertFalse(ImageResource::canDeleteAny());
        $this->get(PageResource::getUrl('create'))->assertForbidden();
    }

    public function test_codes_are_made_from_the_names(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $names = fn (string $en, string $ro, array $more = []) => ['trans' => ['en' => ['name' => $en, ...$more], 'ro' => ['name' => $ro, ...$more]]];

        \Livewire\Livewire::test(\App\Filament\Resources\Meats\Pages\CreateMeat::class)
            ->fillForm($names('Pork', 'Porc'))->call('create')->assertHasNoFormErrors();
        \Livewire\Livewire::test(\App\Filament\Resources\PackTypes\Pages\CreatePackType::class)
            ->fillForm([...$names('Sack', 'Sac'), 'style' => 'sack'])->call('create')->assertHasNoFormErrors();
        $this->assertTrue(Meat::where('key', 'pork')->exists());
        $this->assertTrue(PackType::where('key', 'sack')->exists());

        $product = fn (string $type) => [
            ...$names('Texas BBQ', 'Texas BBQ'),
            'pack_type_id' => PackType::where('key', $type)->value('id'),
            'meat_id' => Meat::where('key', 'pork')->value('id'),
            'price' => 150, 'grams' => 100, 'heat' => 2,
        ];
        foreach (['pack', 'sack', 'pack'] as $type) {
            \Livewire\Livewire::test(\App\Filament\Resources\Products\Pages\CreateProduct::class)
                ->fillForm($product($type))->call('create')->assertHasNoFormErrors();
        }

        $this->assertSame(
            ['pork-texas-bbq', 'pork-texas-bbq-sack', 'pork-texas-bbq-2'],
            Product::where('key', 'like', 'pork-%')->orderBy('id')->pluck('key')->all(),
        );
    }

    public function test_a_meat_in_use_cannot_be_deleted_from_the_list(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(MeatResource::getUrl())->assertOk()->assertSee('Chicken');
        \Livewire\Livewire::test(\App\Filament\Resources\Meats\Pages\ListMeats::class)
            ->assertTableActionHidden('delete', Meat::where('key', 'beef')->sole());
    }
}
