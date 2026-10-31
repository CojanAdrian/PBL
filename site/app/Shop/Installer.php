<?php

namespace App\Shop;

use App\Models\Content;
use App\Models\Ingredient;
use App\Models\Lang;
use App\Models\Meat;
use App\Models\Order;
use App\Models\PackType;
use App\Models\Page;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LangSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sets up Big Ed's starting data: languages, roles, the first admin, the home
 * page's SEO, every site text (EN/RO/RU), the meat and pack types, the
 * ingredients, the products, and the orders from the old Node site. Safe to run again and again: it only adds what is missing and
 * never changes anything that was edited in the admin.
 */
class Installer
{
    /** @var list<string> */
    public array $log = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->languages();
            $this->roles();
            $this->admin();
            $home = $this->homePage();
            $this->texts($home);
            $this->meats();
            $this->packTypes();
            $this->ingredients();
            $this->products();
        });
        $this->importOldOrders();
    }

    protected function languages(): void
    {
        if (Lang::count() === 0) {
            (new LangSeeder)->run();
            $this->log[] = 'Added the languages: English, Română, Русский.';
        }
    }

    protected function roles(): void
    {
        if (Role::count() === 0) {
            (new RoleSeeder)->run();
            $this->log[] = 'Added the Admin and Editor roles.';
        }
    }

    protected function admin(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');
        if (User::count() > 0) {
            return;
        }
        if (blank($email) || blank($password)) {
            $this->log[] = 'No admin user yet: set ADMIN_EMAIL and ADMIN_PASSWORD and run this again.';

            return;
        }
        User::create([
            'email' => $email,
            'name' => 'Admin',
            'password' => $password,
            'role_id' => Role::where('role', Role::ADMIN)->value('id'),
            'active' => true,
            'email_verified_at' => now(),
        ]);
        $this->log[] = "Created the admin user {$email}.";
    }

    protected function homePage(): Page
    {
        $page = Page::firstOrCreate(['label' => 'front.index'], ['active' => true]);
        $meta = $page->meta()->firstOrCreate([]);
        $seo = [
            'en' => ["Big Ed's Jerky | Premium beef & chicken jerky", 'Premium beef and chicken jerky, made by hand at home in Moldova. Texas BBQ, Garlic Teriyaki and Honey Sriracha in 100 g packs, or try them all in the Taster box. Build your box and preorder in under a minute.'],
            'ro' => ["Big Ed's Jerky | Jerky premium de vită și de pui", 'Jerky premium de vită și de pui, făcut manual, acasă, în Moldova. Texas BBQ, Teriyaki cu usturoi și Miere și Sriracha, în pachete de 100 g, sau toate în cutia de degustare. Fă-ți cutia și precomandă în mai puțin de un minut.'],
            'ru' => ["Big Ed's Jerky | Джерки из говядины и курицы", 'Премиальное вяленое мясо из говядины и курицы, сделанное вручную в Молдове. Техас BBQ, чесночный терияки и мёд со шрирачей в упаковках по 100 г или все сразу в дегустационной коробке. Соберите коробку и оформите предзаказ меньше чем за минуту.'],
        ];
        foreach ($this->langCodes() as $code) {
            if (isset($seo[$code]) && ! $meta->text()->where('lang', $code)->exists()) {
                $meta->text()->create(['lang' => $code, 'title' => $seo[$code][0], 'text' => $seo[$code][1]]);
            }
        }

        return $page;
    }

    protected function texts(Page $home): void
    {
        $added = 0;
        $codes = $this->langCodes();
        $existing = Content::with('text')->get()->keyBy('key');
        foreach (Texts::ALL as $key => $versions) {
            $content = $existing->get($key) ?? Content::create(['key' => $key, 'page_id' => $home->id]);
            $have = $content->relationLoaded('text') ? $content->text->pluck('lang')->all() : [];
            foreach ($codes as $code) {
                $text = $versions[$code] ?? null;
                if ($text !== null && $text !== '' && ! in_array($code, $have, true)) {
                    $content->text()->create(['lang' => $code, 'text' => $text]);
                    $added++;
                }
            }
        }
        if ($added) {
            $this->log[] = "Added {$added} texts to Text blocks.";
        }
    }

    /** Beef and chicken, with their pouch colours. */
    protected function meats(): void
    {
        if (Meat::count() > 0) {
            return;
        }
        $meats = [
            'beef' => [['#2a2624', '#0d0b0a', '#0a0908'], ['en' => 'Beef', 'ro' => 'Vită', 'ru' => 'Говядина']],
            'chicken' => [['#7b5a3f', '#4b3424', '#3a281b'], ['en' => 'Chicken', 'ro' => 'Pui', 'ru' => 'Курица']],
        ];
        foreach ($meats as $key => [$colours, $names]) {
            $meat = Meat::create(['key' => $key, 'pouch_top' => $colours[0], 'pouch_bottom' => $colours[1], 'pouch_cap' => $colours[2]]);
            $this->translate($meat, $names);
        }
        $this->log[] = 'Added the meat types: Beef, Chicken.';
    }

    /** A 100 g pack (one flavour) and a box (several flavours). */
    protected function packTypes(): void
    {
        if (PackType::count() > 0) {
            return;
        }
        $pack = PackType::create(['key' => 'pack', 'style' => 'pouch', 'mix' => false]);
        $this->translate($pack, ['en' => 'Pack', 'ro' => 'Pachet', 'ru' => 'Упаковка']);
        $box = PackType::create(['key' => 'box', 'style' => 'box', 'mix' => true]);
        $this->translate($box, ['en' => 'Box', 'ro' => 'Cutie', 'ru' => 'Коробка']);
        $this->log[] = 'Added the pack types: Pack, Box.';
    }

    /** "What goes in", in the story section. */
    protected function ingredients(): void
    {
        if (Ingredient::count() > 0) {
            return;
        }
        $items = [
            ['en' => 'Beef or chicken', 'ro' => 'Vită sau pui', 'ru' => 'Говядина или курица'],
            ['en' => 'Soy sauce', 'ro' => 'Sos de soia', 'ru' => 'Соевый соус'],
            ['en' => 'Garlic', 'ro' => 'Usturoi', 'ru' => 'Чеснок'],
            ['en' => 'Honey', 'ro' => 'Miere', 'ru' => 'Мёд'],
            ['en' => 'Sriracha', 'ro' => 'Sriracha', 'ru' => 'Шрирача'],
            ['en' => 'Brown sugar', 'ro' => 'Zahăr brun', 'ru' => 'Коричневый сахар'],
            ['en' => 'Spices', 'ro' => 'Condimente', 'ru' => 'Специи'],
            ['en' => 'Natural smoke flavour', 'ro' => 'Aromă naturală de fum', 'ru' => 'Натуральный аромат дыма'],
        ];
        foreach ($items as $names) {
            $this->translate(Ingredient::create([]), $names);
        }
        $this->log[] = 'Added the ingredients.';
    }

    /** One `name` translation per language that exists. */
    protected function translate($model, array $names): void
    {
        foreach ($this->langCodes() as $code) {
            if (isset($names[$code])) {
                $model->text()->create(['lang' => $code, 'name' => $names[$code]]);
            }
        }
    }

    protected function products(): void
    {
        if (Product::count() > 0) {
            return;
        }
        $flavours = [
            'texas-bbq' => [
                'heat' => 2, 'accent' => '#d8392b', 'band' => '#b8261c', 'band_text' => '#ffffff',
                'en' => ['Texas BBQ', 'Smoky, sweet, Texas-sized.'],
                'ro' => ['Texas BBQ', 'Afumat, dulce, pe măsura Texasului.'],
                'ru' => ['Техас BBQ', 'Дымный, сладкий, по-техасски.'],
                'notes' => [
                    'beef' => ['en' => 'Brown sugar, smoke and a peppery rub, slow dried onto premium beef.', 'ro' => 'Zahăr brun, fum și condimente piperate, uscate încet pe vită premium.', 'ru' => 'Коричневый сахар, дымок и перечная обсыпка, медленно высушенные на отборной говядине.'],
                    'chicken' => ['en' => 'Lean chicken with a sweet, smoky BBQ glaze and a peppery finish.', 'ro' => 'Pui slab cu glazură BBQ dulce și afumată și un final piperat.', 'ru' => 'Нежирная курица в сладкой дымной BBQ-глазури с перечным послевкусием.'],
                ],
            ],
            'garlic-teriyaki' => [
                'heat' => 1, 'accent' => '#e6cf9f', 'band' => '#e6cf9f', 'band_text' => '#1c1815',
                'en' => ['Garlic Teriyaki', 'Savoury, glossy, garlicky.'],
                'ro' => ['Teriyaki cu usturoi', 'Savuros, lucios, cu mult usturoi.'],
                'ru' => ['Чесночный терияки', 'Пикантный, глянцевый, чесночный.'],
                'notes' => [
                    'beef' => ['en' => 'Soy and roasted garlic in a sticky teriyaki glaze. Deep and savoury, no heat.', 'ro' => 'Soia și usturoi copt într-o glazură teriyaki lipicioasă. Gust bogat, fără iuțeală.', 'ru' => 'Соя и запечённый чеснок в тягучей глазури терияки. Глубокий вкус, без остроты.'],
                    'chicken' => ['en' => 'Tender chicken in a soy and garlic teriyaki glaze. Light, savoury, no heat.', 'ro' => 'Pui fraged în glazură teriyaki cu soia și usturoi. Ușor, savuros, fără iuțeală.', 'ru' => 'Нежная курица в глазури терияки с соей и чесноком. Лёгкий вкус, без остроты.'],
                ],
            ],
            'honey-sriracha' => [
                'heat' => 4, 'accent' => '#f07a2e', 'band' => '#d9601f', 'band_text' => '#ffffff',
                'en' => ['Honey Sriracha', 'Sweet first, then the fire.'],
                'ro' => ['Miere și Sriracha', 'Întâi dulce, apoi focul.'],
                'ru' => ['Мёд и шрирача', 'Сначала сладко, потом огонь.'],
                'notes' => [
                    'beef' => ['en' => 'Golden honey up front, then a sriracha kick that keeps building.', 'ro' => 'Întâi miere aurie, apoi o iuțeală de sriracha care tot crește.', 'ru' => 'Сначала золотистый мёд, затем нарастающая острота шрирачи.'],
                    'chicken' => ['en' => 'Sweet honey heat on lean chicken. Sticky, spicy, hard to stop.', 'ro' => 'Miere dulce și iute pe pui slab. Lipicios, picant, greu de lăsat.', 'ru' => 'Сладкий мёд и острота на нежирной курице. Тягучее, пикантное, не оторваться.'],
                ],
            ],
        ];
        $prices = ['beef' => 130, 'chicken' => 70];
        $codes = $this->langCodes();
        $pack = PackType::where('key', 'pack')->first() ?? PackType::where('mix', false)->orderBy('order_column')->first();
        $boxType = PackType::where('key', 'box')->first() ?? PackType::where('mix', true)->orderBy('order_column')->first();

        if ($boxType) {
            $box = Product::create(['key' => 'taster-box', 'pack_type_id' => $boxType->id, 'price' => 285, 'grams' => 300, 'heat' => 1, 'accent' => '#ff6a2b', 'band' => '#ff6a2b', 'band_text' => '#ffffff']);
            $boxText = [
                'en' => ['Taster box', 'All six flavours in one box.', 'A 50 g pack of every flavour, beef and chicken. The easiest way to find your favourite.'],
                'ro' => ['Cutia de degustare', 'Toate cele șase arome într-o cutie.', 'Câte un pachet de 50 g din fiecare aromă, de vită și de pui. Cel mai simplu mod să-ți găsești preferata.'],
                'ru' => ['Дегустационная коробка', 'Все шесть вкусов в одной коробке.', 'По упаковке 50 г каждого вкуса — говядина и курица. Самый простой способ найти любимый.'],
            ];
            foreach ($codes as $code) {
                if (isset($boxText[$code])) {
                    $box->text()->create(['lang' => $code, 'name' => $boxText[$code][0], 'tagline' => $boxText[$code][1], 'notes' => $boxText[$code][2]]);
                }
            }
        }

        foreach (['beef', 'chicken'] as $meatKey) {
            $meat = Meat::where('key', $meatKey)->first();
            if (! $meat || ! $pack) {
                continue;
            }
            foreach ($flavours as $id => $f) {
                $product = Product::create([
                    'key' => "{$meatKey}-{$id}", 'pack_type_id' => $pack->id, 'meat_id' => $meat->id,
                    'price' => $prices[$meatKey], 'grams' => 100,
                    'heat' => $f['heat'], 'accent' => $f['accent'], 'band' => $f['band'], 'band_text' => $f['band_text'],
                ]);
                foreach ($codes as $code) {
                    if (isset($f[$code])) {
                        $product->text()->create(['lang' => $code, 'name' => $f[$code][0], 'tagline' => $f[$code][1], 'notes' => $f['notes'][$meatKey][$code]]);
                    }
                }
            }
        }
        $this->log[] = 'Added the products: Taster box and the six beef and chicken packs.';
    }

    /**
     * Copies the orders from the old Node site's `preorders` table (same database)
     * into `orders`. Orders already copied are skipped.
     */
    protected function importOldOrders(): void
    {
        if (! Schema::hasTable('preorders')) {
            return;
        }
        $known = Order::pluck('code')->flip();
        $names = Product::with(['text', 'packType', 'meat.text'])->get()->keyBy('key');
        $count = 0;
        foreach (DB::table('preorders')->orderBy('created_at')->get() as $row) {
            if ($known->has($row->code)) {
                continue;
            }
            $items = array_map(function ($i) use ($names) {
                $product = $names->get($i['flavour'] ?? '');

                return [
                    'flavour' => $i['flavour'] ?? '',
                    'name' => $product?->fullName('en') ?? ucwords(str_replace('-', ' ', (string) ($i['flavour'] ?? ''))),
                    'grams' => (int) ($i['grams'] ?? 0),
                    'qty' => (int) ($i['qty'] ?? 0),
                    'price' => (int) ($i['price'] ?? 0),
                    'box' => $product?->isMix() ?? false,
                ];
            }, json_decode($row->items, true) ?: []);
            $order = new Order([
                'code' => $row->code,
                'name' => $row->name,
                'phone' => $row->phone,
                'email' => $row->email ?: null,
                'delivery' => $row->delivery,
                'address' => $row->address ?: null,
                'wanted_date' => $row->wanted_date ?: null,
                'note' => $row->note ?: null,
                'items' => $items,
                'total_grams' => $row->total_grams,
                'total_price' => $row->total_price,
                'currency' => $row->currency,
            ]);
            $order->created_at = $order->updated_at = $row->created_at;
            $order->save();
            $count++;
        }
        if ($count) {
            $this->log[] = "Copied {$count} ".($count === 1 ? "order" : "orders")." from the old site.";
        }
    }

    /** @return list<string> */
    protected function langCodes(): array
    {
        return Lang::pluck('code')->all();
    }
}
