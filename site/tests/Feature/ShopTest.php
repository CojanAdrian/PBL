<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Content;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Shop\Installer;
use App\Shop\Menu;
use App\Shop\Notifier;
use App\Shop\PreorderValidator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ShopTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(Installer::class)->run();
        // No real email, Telegram or Google calls from the tests.
        Http::preventStrayRequests();
    }

    private function order(array $extra = []): array
    {
        return [
            'name' => 'Ana Popescu',
            'phone' => '+373 69 123 456',
            'email' => '',
            'items' => [['flavour' => 'beef-texas-bbq', 'grams' => 100, 'qty' => 2]],
            ...$extra,
        ];
    }

    public function test_the_installer_adds_products_and_texts_once(): void
    {
        $this->assertSame(7, Product::count());
        $this->assertSame(['taster-box', 'beef-texas-bbq', 'beef-garlic-teriyaki', 'beef-honey-sriracha', 'chicken-texas-bbq', 'chicken-garlic-teriyaki', 'chicken-honey-sriracha'], Product::orderBy('order_column')->pluck('key')->all());
        $texts = Content::count();

        // Edited texts are never overwritten by a second run.
        Content::where('key', 'hero_title_1')->sole()->text()->where('lang', 'en')->update(['text' => 'Edited']);
        app(Installer::class)->run();
        $this->assertSame(7, Product::count());
        $this->assertSame($texts, Content::count());
        $this->assertSame('Edited', Content::where('key', 'hero_title_1')->sole()->translation('en')->text);
    }

    public function test_prices_beef_130_chicken_70_taster_box_285(): void
    {
        $price = fn (string $key, int $grams) => PreorderValidator::validate($this->order(['items' => [['flavour' => $key, 'grams' => $grams, 'qty' => 1]]]))['order']['total_price'];

        $this->assertSame(130, $price('beef-texas-bbq', 100));
        $this->assertSame(130, $price('beef-honey-sriracha', 100));
        $this->assertSame(70, $price('chicken-texas-bbq', 100));
        $this->assertSame(285, $price('taster-box', 300));
    }

    public function test_prices_come_from_the_admin(): void
    {
        Product::where('key', 'chicken-texas-bbq')->update(['price' => 75]);

        $result = PreorderValidator::validate($this->order(['items' => [['flavour' => 'chicken-texas-bbq', 'grams' => 100, 'qty' => 2]]]));
        $this->assertSame(150, $result['order']['total_price']);
    }

    public function test_hidden_products_cannot_be_ordered(): void
    {
        Product::where('key', 'beef-texas-bbq')->update(['active' => false]);

        $this->assertArrayHasKey('items', PreorderValidator::validate($this->order())['errors']);
        $this->assertNotContains('beef-texas-bbq', collect(Menu::build()['flavours'])->pluck('id'));
    }

    public function test_items_merge_and_sizes_are_checked(): void
    {
        $result = PreorderValidator::validate($this->order(['items' => [
            ['flavour' => 'chicken-honey-sriracha', 'grams' => 100, 'qty' => 1],
            ['flavour' => 'chicken-honey-sriracha', 'grams' => 100, 'qty' => 2],
            ['flavour' => 'beef-garlic-teriyaki', 'grams' => 100, 'qty' => 1],
        ]]));
        $this->assertCount(2, $result['order']['items']);
        $this->assertSame(70 * 3 + 130, $result['order']['total_price']);
        $this->assertSame('Honey Sriracha Chicken', $result['order']['items'][0]['name']);

        $bad = fn (array $item) => isset(PreorderValidator::validate($this->order(['items' => [$item]]))['errors']['items']);
        $this->assertTrue($bad(['flavour' => 'beef-texas-bbq', 'grams' => 50, 'qty' => 1]), 'custom sizes are off');
        $this->assertTrue($bad(['flavour' => 'taster-box', 'grams' => 100, 'qty' => 1]), 'a box weighs what the admin says');
        $this->assertTrue($bad(['flavour' => 'nope', 'grams' => 100, 'qty' => 1]));
        $this->assertTrue($bad(['flavour' => 'beef-texas-bbq', 'grams' => 100, 'qty' => 0]));
        $this->assertTrue($bad(['flavour' => 'beef-texas-bbq', 'grams' => 100, 'qty' => 21]));
    }

    public function test_custom_sizes_follow_the_100g_price_when_switched_on(): void
    {
        config(['shop.custom.enabled' => true]);
        $price = fn (string $key, int $grams) => PreorderValidator::validate($this->order(['items' => [['flavour' => $key, 'grams' => $grams, 'qty' => 1]]]))['order']['total_price'] ?? null;

        $this->assertSame(175, $price('chicken-texas-bbq', 250));
        $this->assertSame(325, $price('beef-texas-bbq', 250));
        $this->assertNull($price('beef-texas-bbq', 255));
    }

    public function test_name_and_phone_are_required_email_is_optional(): void
    {
        $errors = fn (array $extra) => PreorderValidator::validate($this->order($extra))['errors'] ?? [];

        $this->assertSame([], $errors([]));
        $this->assertSame([], $errors(['email' => 'ana@example.com']));
        $this->assertArrayHasKey('phone', $errors(['phone' => '', 'email' => 'ana@example.com']));
        $this->assertArrayHasKey('phone', $errors(['phone' => 'abc']));
        $this->assertArrayHasKey('name', $errors(['name' => 'A']));
        $this->assertArrayHasKey('email', $errors(['email' => 'not-an-email']));
        $this->assertArrayHasKey('date', $errors(['date' => '2001-01-01']));
        $this->assertArrayHasKey('date', $errors(['date' => 'garbage']));
    }

    public function test_orders_from_any_language_are_saved_in_english(): void
    {
        $this->postJson('/ro/preorder', $this->order(['items' => [['flavour' => 'chicken-honey-sriracha', 'grams' => 100, 'qty' => 1]]]))
            ->assertCreated();

        $this->assertSame(['1 x 100 g Honey Sriracha Chicken'], Order::sole()->lines());
        $this->assertSame('ro', Order::sole()->locale);
    }

    public function test_errors_are_in_the_visitors_language(): void
    {
        $this->postJson('/ro/preorder', $this->order(['phone' => '']))
            ->assertStatus(400)
            ->assertJsonPath('errors.phone', 'Adaugă numărul de telefon ca Ed să te poată contacta.');
    }

    public function test_a_preorder_is_saved_and_everyone_is_told(): void
    {
        config([
            'shop.notify.brevo_key' => 'brevo-key', 'shop.notify.from' => "Big Ed's Jerky <orders@example.com>",
            'shop.notify.telegram_token' => 't0ken', 'shop.notify.telegram_chat_id' => '42',
            'shop.notify.sheets_url' => 'https://script.test/exec', 'shop.notify.sheets_token' => 'tok',
        ]);
        Http::fake([
            'script.test/*' => Http::response(['ok' => true, 'count' => 1, 'added' => 1]),
            '*' => Http::response(['ok' => true]),
        ]);

        $response = $this->postJson('/en/preorder', $this->order([
            'note' => 'Call before 6',
            'items' => [['flavour' => 'taster-box', 'grams' => 300, 'qty' => 1], ['flavour' => 'chicken-texas-bbq', 'grams' => 100, 'qty' => 2]],
        ]))->assertCreated()->assertJsonPath('totalPrice', 425);

        $code = $response->json('code');
        $this->assertMatchesRegularExpression('/^BED-[2-9A-HJ-NP-Z]{5}$/', $code);
        $order = Order::where('code', $code)->sole();
        $this->assertSame('+37369123456', $order->phone);
        $this->assertSame(['1 x Taster box', '2 x 100 g Texas BBQ Chicken'], $order->lines());

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'api.brevo.com')
            && $r['to'][0]['email'] === 'kenny@igtfreight.com'
            && str_contains($r['subject'], $code)
            && str_contains($r['htmlContent'], 'Call before 6'));
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'api.telegram.org/bott0ken') && str_contains($r['text'], '2 x 100 g Texas BBQ Chicken'));
        Http::assertSent(fn (Request $r) => $r->url() === 'https://script.test/exec'
            && $r['token'] === 'tok'
            && $r['orders'][0]['code'] === $code
            && $r['orders'][0]['items'] === "1 x Taster box\n2 x 100 g Texas BBQ Chicken"
            && $r['orders'][0]['total'] === 425);
    }

    public function test_a_broken_email_service_never_loses_an_order(): void
    {
        config(['shop.notify.resend_key' => 'k']);
        Http::fake(['*' => Http::response('domain not verified', 403)]);

        $this->postJson('/en/preorder', $this->order())->assertCreated();

        $this->assertSame(1, Order::count());
        $this->assertStringContainsString('403', app(Notifier::class)->status('email')['lastError']);
    }

    public function test_customer_text_is_escaped_in_the_email(): void
    {
        $order = Order::create([...PreorderValidator::validate($this->order(['name' => '<b>Eve</b>', 'note' => '<script>x</script>']))['order'], 'code' => 'BED-TEST1']);
        $html = view('email.order', ['order' => $order, 'adminUrl' => 'https://x.test/admin/orders'])->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>Eve</b>', $html);
    }

    public function test_the_honeypot_gets_a_fake_success_and_nothing_is_stored(): void
    {
        $this->postJson('/en/preorder', $this->order(['website' => 'http://spam.test']))
            ->assertCreated()->assertJsonPath('code', 'BED-00000');

        $this->assertSame(0, Order::count());
    }

    public function test_invalid_orders_never_use_up_the_limit_saved_ones_do(): void
    {
        RateLimiter::clear('preorder:127.0.0.1');
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/en/preorder', $this->order(['items' => []]))->assertStatus(400);
        }
        for ($i = 0; $i < 15; $i++) {
            $this->postJson('/en/preorder', $this->order())->assertCreated();
        }
        $this->postJson('/en/preorder', $this->order())->assertStatus(429);
    }

    public function test_deleting_an_order_in_the_admin_removes_its_sheet_row(): void
    {
        config(['shop.notify.sheets_url' => 'https://script.test/exec']);
        Http::fake(['*' => Http::response(['ok' => true, 'deleted' => 1])]);
        $order = Order::create([...PreorderValidator::validate($this->order())['order'], 'code' => 'BED-DEL22']);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageOrders::class)->callTableAction('delete', $order);

        $this->assertSame(0, Order::count());
        Http::assertSent(fn (Request $r) => $r['action'] === 'delete' && $r['codes'] === ['BED-DEL22']);
    }

    public function test_sync_sends_every_order_to_the_sheet(): void
    {
        config(['shop.notify.sheets_url' => 'https://script.test/exec']);
        Http::fake(['*' => Http::response(['ok' => true, 'count' => 2, 'added' => 2])]);
        foreach (['BED-AAAA2', 'BED-AAAA3'] as $code) {
            Order::create([...PreorderValidator::validate($this->order())['order'], 'code' => $code]);
        }
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageOrders::class)->callAction('syncSheet')->assertNotified('2 missing orders added');

        Http::assertSent(fn (Request $r) => array_column($r['orders'], 'code') === ['BED-AAAA2', 'BED-AAAA3']);
    }

    public function test_the_admin_pages_render(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Order::create([...PreorderValidator::validate($this->order())['order'], 'code' => 'BED-VIEW2']);

        $this->get('/admin/orders')->assertOk()->assertSee('BED-VIEW2')->assertSee('Google Sheet');
        $this->get('/admin/products')->assertOk()->assertSee('Texas BBQ');
        $this->get('/admin/products/create')->assertOk();
        $this->get('/admin/products/'.Product::where('key', 'taster-box')->value('id').'/edit')->assertOk();
    }
}
