<?php

namespace App\Shop;

/**
 * Every text on the jerky site, with its starting English, Romanian and Russian
 * version. `php artisan shop:install` copies them into Text blocks, where they
 * can be edited (texts already there are never overwritten).
 *
 * Keys starting with `shop_` are used by the order builder's JavaScript; `{name}`
 * style placeholders are filled in by the code and must stay in the text.
 */
class Texts
{
    public const ALL = [
        // ---- Header & menu ----
        'skip_link' => ['en' => 'Skip to content', 'ro' => 'Sari la conținut', 'ru' => 'Перейти к содержимому'],
        'nav_brand' => ['en' => "Big Ed's", 'ro' => "Big Ed's", 'ru' => "Big Ed's"],
        'nav_story' => ['en' => 'Story', 'ro' => 'Povestea', 'ru' => 'История'],
        'nav_flavours' => ['en' => 'Flavours', 'ro' => 'Arome', 'ru' => 'Вкусы'],
        'nav_preorder' => ['en' => 'Preorder', 'ro' => 'Precomandă', 'ru' => 'Предзаказ'],
        'nav_open_menu' => ['en' => 'Open menu', 'ro' => 'Deschide meniul', 'ru' => 'Открыть меню'],
        'nav_languages' => ['en' => 'Language', 'ro' => 'Limba', 'ru' => 'Язык'],
        'menu_tagline' => ['en' => "Premium beef & chicken jerky.\nMade in Moldova.", 'ro' => "Jerky premium de vită și de pui.\nFăcut în Moldova.", 'ru' => "Премиальное вяленое мясо из говядины и курицы.\nСделано в Молдове."],

        // ---- Hero ----
        'hero_eyebrow' => ['en' => 'Beef & chicken jerky · Home-made · Moldova', 'ro' => 'Jerky de vită și de pui · Făcut în casă · Moldova', 'ru' => 'Джерки из говядины и курицы · Домашнее · Молдова'],
        'hero_title_1' => ['en' => 'Real meat.', 'ro' => 'Carne adevărată.', 'ru' => 'Настоящее мясо.'],
        'hero_title_2' => ['en' => 'Bold flavor.', 'ro' => 'Gust îndrăzneț.', 'ru' => 'Яркий вкус.'],
        'hero_lead' => [
            'en' => "Big Ed's makes premium beef and chicken jerky at home, by hand, in small batches. Three bold flavours of each. Pick yours, choose your size, and preorder in under a minute.",
            'ro' => "Big Ed's face jerky premium de vită și de pui acasă, manual, în loturi mici. Trei arome îndrăznețe pentru fiecare. Alege-ți aroma și fă precomanda în mai puțin de un minut.",
            'ru' => "Big Ed's делает премиальное вяленое мясо из говядины и курицы дома, вручную, небольшими партиями. По три ярких вкуса каждого. Выберите свой и оформите предзаказ меньше чем за минуту.",
        ],
        'hero_cta' => ['en' => 'Preorder your pack', 'ro' => 'Precomandă acum', 'ru' => 'Сделать предзаказ'],
        'hero_cta_2' => ['en' => 'Explore flavours', 'ro' => 'Descoperă aromele', 'ru' => 'Посмотреть вкусы'],
        'hero_fact_1_strong' => ['en' => 'Beef & chicken', 'ro' => 'Vită și pui', 'ru' => 'Говядина и курица'],
        'hero_fact_1_text' => ['en' => '', 'ro' => '', 'ru' => ''],
        'hero_fact_2_strong' => ['en' => '3', 'ro' => '3', 'ru' => '3'],
        'hero_fact_2_text' => ['en' => 'flavours', 'ro' => 'arome', 'ru' => 'вкуса'],
        'hero_fact_3_strong' => ['en' => '100 g', 'ro' => '100 g', 'ru' => '100 г'],
        'hero_fact_3_text' => ['en' => 'packs', 'ro' => 'pachete', 'ru' => 'упаковки'],
        'hero_fact_4_text' => ['en' => 'Made in', 'ro' => 'Făcut în', 'ru' => 'Сделано в'],
        'hero_fact_4_strong' => ['en' => 'Moldova', 'ro' => 'Moldova', 'ru' => 'Молдове'],
        'hero_logo_alt' => ['en' => "Big Ed's Jerky logo: a bull in front of mountains. Made in Moldova.", 'ro' => "Logo Big Ed's Jerky: un taur în fața munților. Făcut în Moldova.", 'ru' => "Логотип Big Ed's Jerky: бык на фоне гор. Сделано в Молдове."],
        'hero_badge' => ['en' => 'PREORDERS OPEN • SMALL BATCH • ', 'ro' => 'PRECOMENZI DESCHISE • LOT MIC • ', 'ru' => 'ПРЕДЗАКАЗ ОТКРЫТ • МАЛАЯ ПАРТИЯ • '],
        'hero_badge_aria' => ['en' => 'Preorders are open. Go to the preorder builder.', 'ro' => 'Precomenzile sunt deschise. Mergi la precomandă.', 'ru' => 'Предзаказ открыт. Перейти к оформлению.'],
        'hero_scroll_aria' => ['en' => 'Scroll to the story', 'ro' => 'Derulează la poveste', 'ru' => 'Прокрутить к истории'],

        // ---- Marquee ----
        'marquee_1' => ['en' => 'Beef & chicken', 'ro' => 'Vită și pui', 'ru' => 'Говядина и курица'],
        'marquee_2' => ['en' => 'Bold flavor', 'ro' => 'Gust îndrăzneț', 'ru' => 'Яркий вкус'],
        'marquee_3' => ['en' => 'Made in Moldova', 'ro' => 'Făcut în Moldova', 'ru' => 'Сделано в Молдове'],
        'marquee_4' => ['en' => 'Home-made', 'ro' => 'Făcut în casă', 'ru' => 'Домашнее'],
        'marquee_5' => ['en' => 'Small batch', 'ro' => 'Lot mic', 'ru' => 'Малая партия'],

        // ---- Story ----
        'story_eyebrow' => ['en' => 'The story', 'ro' => 'Povestea', 'ru' => 'История'],
        'story_title_1' => ['en' => 'Straight from', 'ro' => 'Direct din', 'ru' => 'Прямо с'],
        'story_title_2' => ['en' => "Ed's kitchen.", 'ro' => 'bucătăria lui Ed.', 'ru' => 'кухни Эда.'],
        'story_text' => [
            'en' => "No factory. No shortcuts. Just one kitchen, premium beef and chicken, and a lot of patience. Every batch of Big Ed's is made by hand at home in Moldova, so every pack tastes like somebody actually cared.",
            'ro' => "Fără fabrică. Fără scurtături. Doar o bucătărie, carne premium de vită și de pui și multă răbdare. Fiecare lot Big Ed's e făcut manual, acasă, în Moldova, așa că fiecare pachet are gustul grijii adevărate.",
            'ru' => "Никакой фабрики. Никаких компромиссов. Только одна кухня, отборная говядина и курица и много терпения. Каждая партия Big Ed's делается вручную дома в Молдове, поэтому в каждой упаковке чувствуется настоящая забота.",
        ],
        'story_ingredients_title' => ['en' => 'What goes in', 'ro' => 'Ce conține', 'ru' => 'Состав'],
        'story_ingredient_1' => ['en' => 'Beef or chicken', 'ro' => 'Vită sau pui', 'ru' => 'Говядина или курица'],
        'story_ingredient_2' => ['en' => 'Soy sauce', 'ro' => 'Sos de soia', 'ru' => 'Соевый соус'],
        'story_ingredient_3' => ['en' => 'Garlic', 'ro' => 'Usturoi', 'ru' => 'Чеснок'],
        'story_ingredient_4' => ['en' => 'Honey', 'ro' => 'Miere', 'ru' => 'Мёд'],
        'story_ingredient_5' => ['en' => 'Sriracha', 'ro' => 'Sriracha', 'ru' => 'Шрирача'],
        'story_ingredient_6' => ['en' => 'Brown sugar', 'ro' => 'Zahăr brun', 'ru' => 'Коричневый сахар'],
        'story_ingredient_7' => ['en' => 'Spices', 'ro' => 'Condimente', 'ru' => 'Специи'],
        'story_ingredient_8' => ['en' => 'Natural smoke flavour', 'ro' => 'Aromă naturală de fum', 'ru' => 'Натуральный аромат дыма'],
        'story_contains' => ['en' => 'Contains soy.', 'ro' => 'Conține soia.', 'ru' => 'Содержит сою.'],
        'story_photo_alt' => ['en' => 'Dark, spice-crusted strips of jerky on a wooden board next to cloves of garlic', 'ro' => 'Fâșii de jerky cu crustă de condimente pe un fund de lemn, lângă căței de usturoi', 'ru' => 'Полоски вяленого мяса в специях на деревянной доске рядом с зубчиками чеснока'],
        'story_caption' => ['en' => 'Real beef. Bold flavor.', 'ro' => 'Carne adevărată. Gust îndrăzneț.', 'ru' => 'Настоящее мясо. Яркий вкус.'],

        // ---- Flavours ----
        'flavours_eyebrow' => ['en' => 'The flavours', 'ro' => 'Aromele', 'ru' => 'Вкусы'],
        'flavours_title_1' => ['en' => 'Pick your', 'ro' => 'Alege-ți', 'ru' => 'Выбери свой'],
        'flavours_title_2' => ['en' => 'fire.', 'ro' => 'focul.', 'ru' => 'огонь.'],
        'flavours_lead' => ['en' => 'Three flavours, in beef or chicken. Tap one to explore it, then add it to your box.', 'ro' => 'Trei arome, de vită sau de pui. Atinge una ca s-o descoperi, apoi adaug-o în cutie.', 'ru' => 'Три вкуса — из говядины или курицы. Нажмите на любой, чтобы рассмотреть, и добавьте в коробку.'],
        'flavours_add' => ['en' => 'Add to my box', 'ro' => 'Adaugă în cutie', 'ru' => 'Добавить в коробку'],

        // ---- Preorder section ----
        'preorder_eyebrow' => ['en' => 'Preorder', 'ro' => 'Precomandă', 'ru' => 'Предзаказ'],
        'preorder_title_1' => ['en' => 'Build your', 'ro' => 'Fă-ți', 'ru' => 'Собери свою'],
        'preorder_title_2' => ['en' => 'box.', 'ro' => 'cutia.', 'ru' => 'коробку.'],
        'preorder_lead' => ['en' => 'Choose your flavours, pick your sizes, and send your preorder. It takes about a minute.', 'ro' => 'Alege aromele și trimite precomanda. Durează cam un minut.', 'ru' => 'Выберите вкусы и отправьте предзаказ. Это займёт около минуты.'],
        'preorder_noscript' => ['en' => 'The preorder builder needs JavaScript. Please turn it on, or contact Ed directly.', 'ro' => 'Pentru precomandă e nevoie de JavaScript. Te rugăm să-l activezi sau să-l contactezi direct pe Ed.', 'ru' => 'Для предзаказа нужен JavaScript. Включите его или свяжитесь с Эдом напрямую.'],

        // ---- Footer ----
        'footer_tagline' => ['en' => "Premium beef & chicken jerky.\nMade by hand in Moldova.", 'ro' => "Jerky premium de vită și de pui.\nFăcut manual în Moldova.", 'ru' => "Премиальное вяленое мясо из говядины и курицы.\nСделано вручную в Молдове."],
        'footer_legal' => ['en' => "Big Ed's Jerky. Contains soy. Store in a cool, dry place and keep sealed after opening.", 'ro' => "Big Ed's Jerky. Conține soia. A se păstra la loc răcoros și uscat, bine închis după deschidere.", 'ru' => "Big Ed's Jerky. Содержит сою. Хранить в прохладном сухом месте, после вскрытия держать закрытым."],
        'footer_contact_empty' => ['en' => 'Questions? Send a preorder and Ed will get back to you.', 'ro' => 'Întrebări? Trimite o precomandă și Ed îți va răspunde.', 'ru' => 'Есть вопросы? Оформите предзаказ, и Эд с вами свяжется.'],
        // Contact details shown in the footer. Leave empty to hide.
        'footer_phone' => ['en' => '', 'ro' => '', 'ru' => ''],
        'footer_email' => ['en' => '', 'ro' => '', 'ru' => ''],
        'footer_instagram' => ['en' => '', 'ro' => '', 'ru' => ''],
        'footer_telegram' => ['en' => '', 'ro' => '', 'ru' => ''],

        // ---- Meats ----
        'meat_beef' => ['en' => 'Beef', 'ro' => 'Vită', 'ru' => 'Говядина'],
        'meat_chicken' => ['en' => 'Chicken', 'ro' => 'Pui', 'ru' => 'Курица'],

        // ---- Order builder (JavaScript) ----
        'shop_currency' => ['en' => 'lei', 'ro' => 'lei', 'ru' => 'лей'],
        'shop_full_name' => ['en' => '{name} {meat}', 'ro' => '{name} ({meat})', 'ru' => '{name} ({meat})'],
        'shop_nav_count' => ['en' => 'Preorder · {n}', 'ro' => 'Precomandă · {n}', 'ru' => 'Предзаказ · {n}'],
        'shop_steps' => ['en' => 'Preorder progress', 'ro' => 'Pașii precomenzii', 'ru' => 'Шаги предзаказа'],
        'shop_step_1' => ['en' => 'Your box', 'ro' => 'Cutia ta', 'ru' => 'Ваша коробка'],
        'shop_step_2' => ['en' => 'Your details', 'ro' => 'Datele tale', 'ru' => 'Ваши данные'],
        'shop_step_3' => ['en' => 'Confirm', 'ro' => 'Confirmare', 'ru' => 'Подтверждение'],
        'shop_p1_title' => ['en' => 'Fill your box', 'ro' => 'Umple-ți cutia', 'ru' => 'Наполните коробку'],
        'shop_p1_sub' => ['en' => 'Start with a quick pick, or add flavours one by one.', 'ro' => 'Începe cu o alegere rapidă sau adaugă aromele una câte una.', 'ru' => 'Начните с готового набора или добавляйте вкусы по одному.'],
        'shop_p1_sub_sizes' => ['en' => 'Every pack size and amount is up to you.', 'ro' => 'Mărimea și numărul pachetelor le alegi tu.', 'ru' => 'Размер и количество упаковок — на ваш выбор.'],
        'shop_p1_sub_fixed' => ['en' => 'Every pack is {g} g; take as many as you like.', 'ro' => 'Fiecare pachet are {g} g; ia câte vrei.', 'ru' => 'Каждая упаковка — {g} г; берите сколько хотите.'],
        'shop_howto' => ['en' => 'How it works', 'ro' => 'Cum funcționează', 'ru' => 'Как это работает'],
        'shop_howto_1' => ['en' => 'Tap {add} on a flavour', 'ro' => 'Apasă {add} la o aromă', 'ru' => 'Нажмите {add} у вкуса'],
        'shop_howto_2' => ['en' => 'Choose how many', 'ro' => 'Alege câte vrei', 'ru' => 'Выберите количество'],
        'shop_howto_2_sizes' => ['en' => 'Pick a size and how many', 'ro' => 'Alege mărimea și câte vrei', 'ru' => 'Выберите размер и количество'],
        'shop_howto_3' => ['en' => 'Press {continue}', 'ro' => 'Apasă {continue}', 'ru' => 'Нажмите {continue}'],
        'shop_add' => ['en' => 'Add', 'ro' => 'Adaugă', 'ru' => 'Добавить'],
        'shop_continue' => ['en' => 'Continue', 'ro' => 'Continuă', 'ru' => 'Далее'],
        'shop_quick_starts' => ['en' => 'Quick starts', 'ro' => 'Alegeri rapide', 'ru' => 'Готовые наборы'],
        'shop_or_pick' => ['en' => 'Or pick your flavours', 'ro' => 'Sau alege aromele', 'ru' => 'Или выберите вкусы'],
        'shop_group_boxes' => ['en' => 'Boxes', 'ro' => 'Cutii', 'ru' => 'Коробки'],
        'shop_group_meat' => ['en' => '{meat} jerky', 'ro' => 'Jerky de {meat}', 'ru' => '{meat}: джерки'],
        'shop_lineup_title' => ['en' => '{meat} lineup', 'ro' => 'Setul de {meat}', 'ru' => '{meat}'],
        'shop_lineup_desc' => ['en' => 'All {count} flavours, {g} g each', 'ro' => 'Toate cele {count} arome, câte {g} g', 'ru' => 'Все {count} вкуса по {g} г'],
        'shop_continue_details' => ['en' => 'Continue to details', 'ro' => 'Continuă la date', 'ru' => 'Перейти к данным'],
        'shop_p2_title' => ['en' => 'Your details', 'ro' => 'Datele tale', 'ru' => 'Ваши данные'],
        'shop_p2_sub' => ['en' => 'So Ed knows who the box is for and how to reach you.', 'ro' => 'Ca Ed să știe pentru cine e cutia și cum te poate contacta.', 'ru' => 'Чтобы Эд знал, для кого коробка и как с вами связаться.'],
        'shop_name_label' => ['en' => 'Your name', 'ro' => 'Numele tău', 'ru' => 'Ваше имя'],
        'shop_phone_label' => ['en' => 'Phone', 'ro' => 'Telefon', 'ru' => 'Телефон'],
        'shop_phone_hint' => ['en' => 'So Ed can reach you about your order.', 'ro' => 'Ca Ed să te poată contacta despre comandă.', 'ru' => 'Чтобы Эд мог связаться с вами по заказу.'],
        'shop_email_label' => ['en' => 'Email (optional)', 'ro' => 'Email (opțional)', 'ru' => 'Email (необязательно)'],
        'shop_delivery_label' => ['en' => 'Delivery', 'ro' => 'Livrare', 'ru' => 'Доставка'],
        'shop_delivery_note' => ['en' => 'Ed will bring it to you.', 'ro' => 'Ed ți-o aduce.', 'ru' => 'Эд привезёт заказ вам.'],
        'shop_date_label' => ['en' => 'Preferred date (optional)', 'ro' => 'Data preferată (opțional)', 'ru' => 'Желаемая дата (необязательно)'],
        'shop_date_hint' => ['en' => 'Ed will do his best, and confirms the real date with you.', 'ro' => 'Ed va face tot posibilul și îți confirmă data exactă.', 'ru' => 'Эд постарается и подтвердит точную дату с вами.'],
        'shop_note_label' => ['en' => 'Anything Ed should know? (optional)', 'ro' => 'Ceva ce ar trebui să știe Ed? (opțional)', 'ru' => 'Что-то, что Эду стоит знать? (необязательно)'],
        'shop_back' => ['en' => 'Back', 'ro' => 'Înapoi', 'ru' => 'Назад'],
        'shop_review_order' => ['en' => 'Review order', 'ro' => 'Verifică comanda', 'ru' => 'Проверить заказ'],
        'shop_p3_title' => ['en' => 'Check and confirm', 'ro' => 'Verifică și confirmă', 'ru' => 'Проверьте и подтвердите'],
        'shop_p3_sub' => ['en' => 'One last look. Nothing is charged online.', 'ro' => 'O ultimă privire. Nu se plătește nimic online.', 'ru' => 'Последний взгляд. Онлайн ничего не списывается.'],
        'shop_summary' => ['en' => 'Your box summary', 'ro' => 'Rezumatul cutiei', 'ru' => 'Сводка по коробке'],
        'shop_your_box' => ['en' => 'Your box', 'ro' => 'Cutia ta', 'ru' => 'Ваша коробка'],
        'shop_tray_empty' => ['en' => "Your box is empty.\nPick a flavour or try a quick start.", 'ro' => "Cutia ta e goală.\nAlege o aromă sau un set rapid.", 'ru' => "Ваша коробка пуста.\nВыберите вкус или готовый набор."],
        'shop_weight' => ['en' => 'Weight', 'ro' => 'Greutate', 'ru' => 'Вес'],
        'shop_total' => ['en' => 'Total', 'ro' => 'Total', 'ru' => 'Итого'],
        'shop_box_empty' => ['en' => 'Your box is empty', 'ro' => 'Cutia ta e goală', 'ru' => 'Коробка пуста'],
        'shop_per_box' => ['en' => 'per box', 'ro' => 'per cutie', 'ru' => 'за коробку'],
        'shop_unit_box' => ['en' => 'box', 'ro' => 'cutie', 'ru' => 'коробку'],
        'shop_unit_pack' => ['en' => 'pack', 'ro' => 'pachet', 'ru' => 'упаковку'],
        'shop_pack_size' => ['en' => 'Pack size', 'ro' => 'Mărimea pachetului', 'ru' => 'Размер упаковки'],
        'shop_popular' => ['en' => 'Popular', 'ro' => 'Popular', 'ru' => 'Популярно'],
        'shop_custom' => ['en' => 'Custom', 'ro' => 'Altă mărime', 'ru' => 'Свой размер'],
        'shop_custom_size' => ['en' => 'Custom size per pack', 'ro' => 'Mărime personalizată', 'ru' => 'Свой размер упаковки'],
        'shop_custom_aria' => ['en' => 'Custom pack size in grams for {name}', 'ro' => 'Mărimea pachetului în grame pentru {name}', 'ru' => 'Размер упаковки в граммах для {name}'],
        'shop_grams_aria' => ['en' => 'Grams per pack', 'ro' => 'Grame per pachet', 'ru' => 'Граммов в упаковке'],
        'shop_add_aria' => ['en' => 'Add {name} to your box', 'ro' => 'Adaugă {name} în cutie', 'ru' => 'Добавить {name} в коробку'],
        'shop_remove_aria' => ['en' => 'Remove {name} from your box', 'ro' => 'Scoate {name} din cutie', 'ru' => 'Убрать {name} из коробки'],
        'shop_qty_boxes' => ['en' => 'Boxes', 'ro' => 'Cutii', 'ru' => 'Коробки'],
        'shop_qty_packs' => ['en' => 'Packs', 'ro' => 'Pachete', 'ru' => 'Упаковки'],
        'shop_fewer_aria' => ['en' => 'One fewer {name} {unit}', 'ro' => 'Cu unul mai puțin: {name}', 'ru' => 'На одну меньше: {name}'],
        'shop_more_aria' => ['en' => 'One more {name} {unit}', 'ro' => 'Încă unul: {name}', 'ru' => 'Ещё одну: {name}'],
        'shop_more_n' => ['en' => '+{n} more', 'ro' => 'încă +{n}', 'ru' => 'ещё +{n}'],
        'shop_packs_one' => ['en' => '{n} pack', 'ro' => '{n} pachet', 'ru' => '{n} упаковка'],
        'shop_packs_few' => ['en' => '{n} packs', 'ro' => '{n} pachete', 'ru' => '{n} упаковки'],
        'shop_packs_other' => ['en' => '{n} packs', 'ro' => '{n} de pachete', 'ru' => '{n} упаковок'],
        'shop_qty_box' => ['en' => '{n} × box', 'ro' => '{n} × cutie', 'ru' => '{n} × коробка'],
        'shop_confirm' => ['en' => 'Confirm preorder', 'ro' => 'Confirmă precomanda', 'ru' => 'Подтвердить предзаказ'],
        'shop_sending' => ['en' => 'Sending…', 'ro' => 'Se trimite…', 'ru' => 'Отправка…'],
        'shop_help_1' => ['en' => 'You can change everything before sending.', 'ro' => 'Poți schimba orice înainte de trimitere.', 'ru' => 'До отправки всё можно изменить.'],
        'shop_help_1_empty' => ['en' => 'Add at least one pack to continue.', 'ro' => 'Adaugă cel puțin un pachet ca să continui.', 'ru' => 'Добавьте хотя бы одну упаковку, чтобы продолжить.'],
        'shop_help_2' => ['en' => "No payment now. You'll review everything next.", 'ro' => 'Nu plătești acum. Urmează să verifici totul.', 'ru' => 'Сейчас ничего платить не нужно. Дальше вы всё проверите.'],
        'shop_help_3' => ['en' => 'Nothing is charged online.', 'ro' => 'Nu se plătește nimic online.', 'ru' => 'Онлайн ничего не списывается.'],
        'shop_too_big' => ['en' => "That's a big box! For large orders please contact Ed directly.", 'ro' => 'E o cutie mare! Pentru comenzi mari, contactează-l direct pe Ed.', 'ru' => 'Большая коробка! Для крупных заказов свяжитесь с Эдом напрямую.'],
        'shop_toast_another' => ['en' => 'Another {name} added. Now {qty}.', 'ro' => 'Încă un {name} adăugat. Acum: {qty}.', 'ru' => 'Добавлено ещё: {name}. Теперь {qty}.'],
        'shop_toast_added' => ['en' => '{name} added to your box.', 'ro' => '{name} a fost adăugat în cutie.', 'ru' => '{name}: добавлено в коробку.'],
        'shop_toast_preset' => ['en' => '{name} loaded. Tweak anything you like.', 'ro' => '{name} a fost încărcat. Modifică orice vrei.', 'ru' => '{name}: набор добавлен. Меняйте что угодно.'],
        'shop_err_name' => ['en' => 'Please tell us your name.', 'ro' => 'Te rugăm să ne spui numele tău.', 'ru' => 'Пожалуйста, укажите ваше имя.'],
        'shop_err_phone_missing' => ['en' => 'Add your phone number so Ed can reach you.', 'ro' => 'Adaugă numărul de telefon ca Ed să te poată contacta.', 'ru' => 'Укажите номер телефона, чтобы Эд мог с вами связаться.'],
        'shop_err_phone' => ['en' => "That phone number doesn't look right.", 'ro' => 'Numărul de telefon nu pare corect.', 'ru' => 'Номер телефона выглядит неверно.'],
        'shop_err_email' => ['en' => "That email doesn't look right.", 'ro' => 'Adresa de email nu pare corectă.', 'ru' => 'Email выглядит неверно.'],
        'shop_err_date' => ['en' => 'Pick a date from today onwards.', 'ro' => 'Alege o dată de azi încolo.', 'ru' => 'Выберите дату не раньше сегодняшней.'],
        'shop_err_items' => ['en' => "Something in your box isn't valid. Please check it and try again.", 'ro' => 'Ceva din cutie nu e valid. Verific-o și încearcă din nou.', 'ru' => 'В коробке что-то не так. Проверьте её и попробуйте снова.'],
        'shop_err_items_empty' => ['en' => 'Add at least one pack to your box.', 'ro' => 'Adaugă cel puțin un pachet în cutie.', 'ru' => 'Добавьте в коробку хотя бы одну упаковку.'],
        'shop_fix_fields' => ['en' => 'Please fix the highlighted fields.', 'ro' => 'Te rugăm să corectezi câmpurile marcate.', 'ru' => 'Исправьте отмеченные поля.'],
        'shop_check_box' => ['en' => 'Please check your box.', 'ro' => 'Te rugăm să verifici cutia.', 'ru' => 'Проверьте вашу коробку.'],
        'shop_edit' => ['en' => 'Edit', 'ro' => 'Modifică', 'ru' => 'Изменить'],
        'shop_label_name' => ['en' => 'Name', 'ro' => 'Nume', 'ru' => 'Имя'],
        'shop_label_email' => ['en' => 'Email', 'ro' => 'Email', 'ru' => 'Email'],
        'shop_delivery_value' => ['en' => 'Ed brings it to you', 'ro' => 'Ed ți-o aduce', 'ru' => 'Эд привезёт вам'],
        'shop_label_date' => ['en' => 'Preferred date', 'ro' => 'Data preferată', 'ru' => 'Желаемая дата'],
        'shop_label_note' => ['en' => 'Note', 'ro' => 'Notă', 'ru' => 'Примечание'],
        'shop_try_again' => ['en' => 'Try again', 'ro' => 'Încearcă din nou', 'ru' => 'Попробовать снова'],
        'shop_err_generic' => ['en' => 'Something went wrong. Please try again.', 'ro' => 'Ceva n-a mers. Te rugăm să încerci din nou.', 'ru' => 'Что-то пошло не так. Попробуйте ещё раз.'],
        'shop_err_timeout' => ['en' => 'That took too long. Check your connection and try again.', 'ro' => 'A durat prea mult. Verifică conexiunea și încearcă din nou.', 'ru' => 'Слишком долго. Проверьте соединение и попробуйте снова.'],
        'shop_err_network' => ['en' => 'Could not reach the server. Check your connection and try again.', 'ro' => 'Serverul nu răspunde. Verifică conexiunea și încearcă din nou.', 'ru' => 'Не удалось связаться с сервером. Проверьте соединение и попробуйте снова.'],
        'shop_err_too_many' => ['en' => "You've sent a lot of preorders from this connection. Please try again later.", 'ro' => 'Ai trimis multe precomenzi de pe această conexiune. Încearcă mai târziu.', 'ru' => 'С этого подключения отправлено много предзаказов. Попробуйте позже.'],
        'shop_done_title' => ['en' => "You're in, {name}!", 'ro' => 'Gata, {name}!', 'ru' => 'Готово, {name}!'],
        'shop_friend' => ['en' => 'friend', 'ro' => 'prietene', 'ru' => 'друг'],
        'shop_done_lead' => ['en' => 'Your preorder is with Ed. Keep this code handy.', 'ro' => 'Precomanda ta a ajuns la Ed. Păstrează acest cod.', 'ru' => 'Ваш предзаказ у Эда. Сохраните этот код.'],
        'shop_done_code' => ['en' => 'Your order code', 'ro' => 'Codul comenzii', 'ru' => 'Код заказа'],
        'shop_copy_code' => ['en' => 'Copy code', 'ro' => 'Copiază codul', 'ru' => 'Скопировать код'],
        'shop_done_1' => ['en' => 'Ed gets your preorder right away.', 'ro' => 'Ed primește precomanda imediat.', 'ru' => 'Эд сразу получит ваш предзаказ.'],
        'shop_done_2' => ['en' => "If he needs anything, he'll reach you on {phone}.", 'ro' => 'Dacă are nevoie de ceva, te contactează la {phone}.', 'ru' => 'Если что-то понадобится, он свяжется с вами по номеру {phone}.'],
        'shop_done_3' => ['en' => 'Your jerky is delivered, and you pay then.', 'ro' => 'Jerky-ul îți este livrat și plătești atunci.', 'ru' => 'Вам доставят джерки, и вы оплатите при получении.'],
        'shop_again' => ['en' => 'Build another box', 'ro' => 'Fă încă o cutie', 'ru' => 'Собрать ещё одну'],
        'shop_copied' => ['en' => 'Code copied.', 'ro' => 'Cod copiat.', 'ru' => 'Код скопирован.'],
        'shop_your_code' => ['en' => 'Your code is {code}', 'ro' => 'Codul tău este {code}', 'ru' => 'Ваш код: {code}'],
        'shop_add_first' => ['en' => 'Add a flavour to your box first.', 'ro' => 'Adaugă mai întâi o aromă în cutie.', 'ru' => 'Сначала добавьте вкус в коробку.'],
        'shop_welcome_back' => ['en' => 'Welcome back. Your box is saved.', 'ro' => 'Bine ai revenit. Cutia ta e salvată.', 'ru' => 'С возвращением! Ваша коробка сохранена.'],
        'shop_payment_note' => ['en' => 'No payment online. You pay when Ed delivers your order.', 'ro' => 'Fără plată online. Plătești când Ed îți livrează comanda.', 'ru' => 'Без онлайн-оплаты. Вы платите, когда Эд привезёт заказ.'],
        'shop_swipe' => ['en' => 'Swipe the pack to explore', 'ro' => 'Glisează pachetul ca să vezi mai multe', 'ru' => 'Листайте упаковку, чтобы посмотреть'],
        'shop_prev' => ['en' => 'Previous flavour', 'ro' => 'Aroma anterioară', 'ru' => 'Предыдущий вкус'],
        'shop_next' => ['en' => 'Next flavour', 'ro' => 'Aroma următoare', 'ru' => 'Следующий вкус'],
        'shop_show' => ['en' => 'Show {name}', 'ro' => 'Arată {name}', 'ru' => 'Показать {name}'],
        'shop_pouch' => ['en' => '{name} jerky pouch', 'ro' => 'Pachet de jerky {name}', 'ru' => 'Упаковка джерки {name}'],
        'shop_add_to_box' => ['en' => 'Add {name} to my box', 'ro' => 'Adaugă {name} în cutie', 'ru' => 'Добавить {name} в коробку'],
        'shop_heat' => ['en' => 'Heat {n}/5', 'ro' => 'Iuțeală {n}/5', 'ru' => 'Острота {n}/5'],
        'shop_heat_aria' => ['en' => 'Heat {n} out of 5', 'ro' => 'Iuțeală {n} din 5', 'ru' => 'Острота {n} из 5'],
    ];

    /** @return array<string, string> key => text in that language (English when there is none). */
    public static function forLocale(string $locale): array
    {
        return array_map(fn (array $t) => $t[$locale] ?? $t['en'], self::ALL);
    }

    public static function en(string $key): string
    {
        return self::ALL[$key]['en'] ?? '';
    }
}
