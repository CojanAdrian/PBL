<?php

namespace App\Shop;

/**
 * Texts for the redesigned page at /{locale}/betatest (English, Romanian, Russian).
 * Kept apart from Text blocks while the design is being tested; once it replaces
 * the main page they can move into Texts.php so they become editable in the admin.
 * Order-form errors, plurals and the currency still come from the shop_… Text blocks.
 *
 * Keys flavour_{texas|garlic|honey}_… belong to the flavour whose product key
 * contains that word (beef-texas-bbq, chicken-garlic-teriyaki, …).
 */
class BetaTexts
{
    public const ALL = [
        'title' => ['en' => "Big Ed's Jerky · homemade beef & chicken jerky from Moldova", 'ro' => "Big Ed's Jerky · jerky de casă din vită și pui, din Moldova", 'ru' => "Big Ed's Jerky · домашнее джерки из говядины и курицы, Молдова"],
        'description' => ['en' => 'Beef and chicken jerky made by hand in small batches in Moldova. Three flavours. Order online, pay on delivery.', 'ro' => 'Jerky de vită și de pui făcut manual, în loturi mici, în Moldova. Trei arome. Comanzi online, plătești la livrare.', 'ru' => 'Джерки из говядины и курицы, сделанное вручную небольшими партиями в Молдове. Три вкуса. Заказ онлайн, оплата при доставке.'],

        'beta_bar' => ['en' => 'New design (beta).', 'ro' => 'Design nou (beta).', 'ru' => 'Новый дизайн (бета).'],
        'beta_back' => ['en' => 'Back to the current site', 'ro' => 'Înapoi la site-ul actual', 'ru' => 'Вернуться на текущий сайт'],

        'nav_menu' => ['en' => 'Menu', 'ro' => 'Meniu', 'ru' => 'Меню'],
        'nav_how' => ['en' => 'How to order', 'ro' => 'Cum comanzi', 'ru' => 'Как заказать'],
        'nav_story' => ['en' => "Ed's kitchen", 'ro' => 'Bucătăria lui Ed', 'ru' => 'Кухня Эда'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'nav_order' => ['en' => 'Order', 'ro' => 'Comandă', 'ru' => 'Заказ'],

        // ---- Hero ----
        'hero_kicker' => ['en' => 'Homemade beef & chicken jerky · Moldova', 'ro' => 'Jerky de casă din vită și pui · Moldova', 'ru' => 'Домашнее джерки из говядины и курицы · Молдова'],
        'hero_title_1' => ['en' => 'Dried slow.', 'ro' => 'Uscat încet.', 'ru' => 'Сушится долго.'],
        'hero_title_2' => ['en' => 'Eaten fast.', 'ro' => 'Mâncat repede.', 'ru' => 'Съедается быстро.'],
        'hero_lead' => ['en' => "Ed makes jerky by hand in his kitchen, a small batch at a time. Three flavours, in beef or chicken. Order here, and pay when it's delivered.", 'ro' => 'Ed face jerky manual, în bucătăria lui, câte un lot mic o dată. Trei arome, de vită sau de pui. Comanzi aici și plătești la livrare.', 'ru' => 'Эд делает джерки вручную на своей кухне, небольшими партиями. Три вкуса, из говядины или курицы. Заказ здесь, оплата при доставке.'],
        'hero_cta' => ['en' => 'See the menu', 'ro' => 'Vezi meniul', 'ru' => 'Смотреть меню'],
        'hero_from' => ['en' => 'from {price} a pack', 'ro' => 'de la {price} pachetul', 'ru' => 'от {price} за упаковку'],
        'hero_pay' => ['en' => 'Pay on delivery', 'ro' => 'Plată la livrare', 'ru' => 'Оплата при доставке'],
        'hero_after' => ['en' => 'Real meat, sliced thin, marinated and dried until it chews just right.', 'ro' => 'Carne adevărată, tăiată subțire, marinată și uscată până ajunge numai bună de mestecat.', 'ru' => 'Настоящее мясо, тонко нарезанное, замаринованное и высушенное до идеальной текстуры.'],
        'hero_scroll' => ['en' => 'Scroll', 'ro' => 'Derulează', 'ru' => 'Листайте'],
        'hero_photo_alt' => ['en' => 'Strips of spiced beef jerky with a red chilli on dark slate', 'ro' => 'Fâșii de jerky de vită condimentat cu un ardei iute pe ardezie închisă', 'ru' => 'Полоски пряного говяжьего джерки с красным перцем на тёмном сланце'],

        // ---- New to jerky ----
        'new_title' => ['en' => 'New to jerky?', 'ro' => 'Nu ai mai gustat jerky?', 'ru' => 'Впервые пробуете джерки?'],
        'new_1_t' => ['en' => 'What it is', 'ro' => 'Ce este', 'ru' => 'Что это'],
        'new_1_d' => ['en' => 'Thin strips of beef or chicken, marinated, then dried slowly until chewy and full of flavour. A snack, not a meal.', 'ro' => 'Fâșii subțiri de vită sau de pui, marinate, apoi uscate încet până devin elastice și pline de gust. O gustare, nu o masă.', 'ru' => 'Тонкие полоски говядины или курицы: маринуются, а потом медленно сушатся, пока не станут упругими и насыщенными. Это закуска, а не обед.'],
        'new_2_t' => ['en' => 'How to eat it', 'ro' => 'Cum se mănâncă', 'ru' => 'Как есть'],
        'new_2_d' => ['en' => 'Straight from the pack. On the road, at work, after the gym, with a beer. No fridge, no plate.', 'ro' => 'Direct din pachet. La drum, la birou, după sală, la o bere. Fără frigider, fără farfurie.', 'ru' => 'Прямо из упаковки. В дороге, на работе, после зала, к пиву. Без холодильника и тарелки.'],
        'new_3_t' => ['en' => "Why Ed's", 'ro' => "De ce Big Ed's", 'ru' => "Почему Big Ed's"],
        'new_3_d' => ['en' => 'Made by hand in one kitchen, from premium beef and chicken. The man who makes it is the man who brings it to you.', 'ro' => 'Făcut manual într-o singură bucătărie, din carne premium de vită și de pui. Cel care îl face e cel care ți-l aduce.', 'ru' => 'Сделано вручную на одной кухне из отборной говядины и курицы. Тот, кто его делает, сам и привозит его вам.'],

        // ---- Menu ----
        'menu_title' => ['en' => 'The menu', 'ro' => 'Meniul', 'ru' => 'Меню'],
        'menu_title_em' => ['en' => 'three ways', 'ro' => 'trei feluri', 'ru' => 'три вкуса'],
        'menu_lead' => ['en' => 'Every flavour comes in beef or chicken, {g} g a pack. Choose one, add as many as you like.', 'ro' => 'Fiecare aromă e de vită sau de pui, {g} g pachetul. Alege și adaugă câte vrei.', 'ru' => 'Каждый вкус есть из говядины и из курицы, {g} г в упаковке. Выберите и добавьте сколько хотите.'],
        'menu_noscript' => ['en' => 'The menu needs JavaScript. Please turn it on, or contact Ed directly.', 'ro' => 'Meniul are nevoie de JavaScript. Activează-l sau contactează-l direct pe Ed.', 'ru' => 'Для меню нужен JavaScript. Включите его или свяжитесь с Эдом напрямую.'],
        'flavour_texas_sticker' => ['en' => 'Smoky & sweet', 'ro' => 'Afumat și dulce', 'ru' => 'Дымный и сладкий'],
        'flavour_texas_chips' => ['en' => 'Brown sugar|Smoke|Black pepper', 'ro' => 'Zahăr brun|Fum|Piper negru', 'ru' => 'Коричневый сахар|Дымок|Чёрный перец'],
        'flavour_garlic_sticker' => ['en' => 'No heat', 'ro' => 'Deloc iute', 'ru' => 'Без остроты'],
        'flavour_garlic_chips' => ['en' => 'Soy|Roasted garlic|Teriyaki glaze', 'ro' => 'Soia|Usturoi copt|Glazură teriyaki', 'ru' => 'Соя|Запечённый чеснок|Глазурь терияки'],
        'flavour_honey_sticker' => ['en' => 'The hot one', 'ro' => 'Cel iute', 'ru' => 'Самый острый'],
        'flavour_honey_chips' => ['en' => 'Golden honey|Sriracha', 'ro' => 'Miere aurie|Sriracha', 'ru' => 'Золотистый мёд|Шрирача'],
        'heat_1' => ['en' => 'No heat', 'ro' => 'Deloc iute', 'ru' => 'Не острый'],
        'heat_2' => ['en' => 'Mild', 'ro' => 'Puțin iute', 'ru' => 'Слегка острый'],
        'heat_3' => ['en' => 'Medium', 'ro' => 'Mediu', 'ru' => 'Средний'],
        'heat_4' => ['en' => 'Hot', 'ro' => 'Iute', 'ru' => 'Острый'],
        'heat_5' => ['en' => 'Very hot', 'ro' => 'Foarte iute', 'ru' => 'Очень острый'],
        'heat_aria' => ['en' => 'Heat: {word}', 'ro' => 'Iuțeală: {word}', 'ru' => 'Острота: {word}'],
        'meat_aria' => ['en' => 'Meat for {name}', 'ro' => 'Carnea pentru {name}', 'ru' => 'Мясо для {name}'],
        'add' => ['en' => 'Add to order', 'ro' => 'Adaugă în comandă', 'ru' => 'Добавить в заказ'],
        'in_order' => ['en' => 'in your order', 'ro' => 'în comandă', 'ru' => 'в заказе'],
        'fewer' => ['en' => 'One fewer {name}', 'ro' => 'Cu unul mai puțin: {name}', 'ru' => 'На одну меньше: {name}'],
        'more' => ['en' => 'One more {name}', 'ro' => 'Încă unul: {name}', 'ru' => 'Ещё одну: {name}'],
        'photo_note' => ['en' => 'Serving suggestion', 'ro' => 'Sugestie de servire', 'ru' => 'Вариант подачи'],

        // ---- Taster box ----
        'taster_kicker' => ['en' => "Can't choose?", 'ro' => 'Nu te poți decide?', 'ru' => 'Не можете выбрать?'],
        'taster_title' => ['en' => 'Take all six.', 'ro' => 'Ia-le pe toate șase.', 'ru' => 'Берите все шесть.'],

        // ---- How to order ----
        'how_title' => ['en' => 'How to order', 'ro' => 'Cum comanzi', 'ru' => 'Как заказать'],
        'how_title_em' => ['en' => 'in a minute', 'ro' => 'într-un minut', 'ru' => 'за минуту'],
        'how_1_t' => ['en' => 'Pick your packs', 'ro' => 'Alegi pachetele', 'ru' => 'Выберите упаковки'],
        'how_1_d' => ['en' => 'Add flavours from the menu, then leave your name and phone. No account, no card.', 'ro' => 'Adaugi arome din meniu, apoi lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Добавьте вкусы из меню и оставьте имя и телефон. Без регистрации и без карты.'],
        'how_2_t' => ['en' => 'Ed calls you', 'ro' => 'Ed te sună', 'ru' => 'Эд вам звонит'],
        'how_2_d' => ['en' => 'He confirms the order and agrees the delivery day with you.', 'ro' => 'Confirmă comanda și stabilește cu tine ziua livrării.', 'ru' => 'Он подтверждает заказ и договаривается с вами о дне доставки.'],
        'how_3_t' => ['en' => 'Pay on delivery', 'ro' => 'Plătești la livrare', 'ru' => 'Оплата при доставке'],
        'how_3_d' => ['en' => 'Ed brings the jerky to you, and you pay when you have it in your hands.', 'ro' => 'Ed îți aduce jerky-ul, iar tu plătești când îl ai în mână.', 'ru' => 'Эд привозит джерки, и вы платите, когда заказ у вас в руках.'],
        'how_cta' => ['en' => 'Start your order', 'ro' => 'Începe comanda', 'ru' => 'Начать заказ'],

        // ---- Ed's kitchen ----
        'story_kicker' => ['en' => "Ed's kitchen", 'ro' => 'Bucătăria lui Ed', 'ru' => 'Кухня Эда'],
        'story_title' => ['en' => 'One kitchen.', 'ro' => 'O bucătărie.', 'ru' => 'Одна кухня.'],
        'story_title_em' => ['en' => 'One Ed.', 'ro' => 'Un Ed.', 'ru' => 'Один Эд.'],
        'story_1' => ['en' => "Big Ed's is made by hand in Ed's home kitchen in Moldova, one small batch at a time, from premium beef and chicken.", 'ro' => "Big Ed's e făcut manual în bucătăria lui Ed din Moldova, câte un lot mic o dată, din carne premium de vită și de pui.", 'ru' => "Big Ed's делается вручную на домашней кухне Эда в Молдове, небольшими партиями, из отборной говядины и курицы."],
        'story_2' => ['en' => 'No factory and no shop in between: the person who makes your jerky is the one who calls you and brings it to your door.', 'ro' => 'Nicio fabrică și niciun magazin la mijloc: cel care îți face jerky-ul e cel care te sună și ți-l aduce la ușă.', 'ru' => 'Ни фабрики, ни магазина посередине: тот, кто делает ваше джерки, сам звонит вам и привозит его к двери.'],
        'step_1_t' => ['en' => 'Sliced thin', 'ro' => 'Tăiat subțire', 'ru' => 'Тонкая нарезка'],
        'step_1_d' => ['en' => 'Lean cuts of beef and chicken, cut into thin strips.', 'ro' => 'Bucăți slabe de vită și de pui, tăiate în fâșii subțiri.', 'ru' => 'Нежирное мясо говядины и курицы нарезается тонкими полосками.'],
        'step_2_t' => ['en' => 'Marinated', 'ro' => 'Marinat', 'ru' => 'Маринад'],
        'step_2_d' => ['en' => 'Each flavour gets its own marinade: garlic, soy, honey, sriracha, spices.', 'ro' => 'Fiecare aromă are marinada ei: usturoi, soia, miere, sriracha, condimente.', 'ru' => 'У каждого вкуса свой маринад: чеснок, соя, мёд, шрирача, специи.'],
        'step_3_t' => ['en' => 'Dried slow', 'ro' => 'Uscat încet', 'ru' => 'Медленная сушка'],
        'step_3_d' => ['en' => 'Then it dries slowly, until it is chewy and the flavour is locked in.', 'ro' => 'Apoi se usucă încet, până devine elastic și gustul rămâne închis înăuntru.', 'ru' => 'Затем мясо медленно сушится, пока не станет упругим, а вкус не запечатается внутри.'],
        'step_4_t' => ['en' => 'Brought to you', 'ro' => 'Adus la tine', 'ru' => 'Доставка'],
        'step_4_d' => ['en' => 'Packed in 100 g pouches and delivered by Ed himself.', 'ro' => 'Ambalat în pachete de 100 g și livrat chiar de Ed.', 'ru' => 'Упаковывается в пакеты по 100 г, и Эд сам привозит заказ.'],
        'ingredients_title' => ['en' => 'What goes in', 'ro' => 'Ce conține', 'ru' => 'Состав'],
        'contains_soy' => ['en' => 'All flavours contain soy.', 'ro' => 'Toate aromele conțin soia.', 'ru' => 'Все вкусы содержат сою.'],
        'store' => ['en' => 'Keep in a cool, dry place.', 'ro' => 'A se păstra la loc răcoros și uscat.', 'ru' => 'Хранить в прохладном сухом месте.'],

        // ---- FAQ ----
        'faq_title' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'faq_1_q' => ['en' => 'Why is it a preorder?', 'ro' => 'De ce e precomandă?', 'ru' => 'Почему это предзаказ?'],
        'faq_1_a' => ['en' => 'Ed makes jerky in small batches, not from a warehouse. You order now, and when he calls he tells you when yours will be ready.', 'ro' => 'Ed face jerky în loturi mici, nu dintr-un depozit. Comanzi acum, iar când te sună îți spune când e gata al tău.', 'ru' => 'Эд делает джерки небольшими партиями, а не со склада. Вы заказываете сейчас, а когда он позвонит, скажет, когда ваш заказ будет готов.'],
        'faq_2_q' => ['en' => 'How do I pay?', 'ro' => 'Cum plătesc?', 'ru' => 'Как оплатить?'],
        'faq_2_a' => ['en' => 'When your order is delivered. Nothing is paid on this website.', 'ro' => 'Când îți este livrată comanda. Pe site nu se plătește nimic.', 'ru' => 'При получении заказа. На сайте ничего оплачивать не нужно.'],
        'faq_3_q' => ['en' => 'Beef or chicken?', 'ro' => 'Vită sau pui?', 'ru' => 'Говядина или курица?'],
        'faq_3_a' => ['en' => 'Same three flavours. Beef is richer and chewier; chicken is lighter and leaner. The taster box has both.', 'ro' => 'Aceleași trei arome. Vita e mai bogată și mai consistentă, puiul e mai ușor și mai slab. Cutia de degustare le are pe amândouă.', 'ru' => 'Те же три вкуса. Говядина насыщеннее и плотнее, курица легче и нежирнее. В дегустационной коробке есть и то и другое.'],
        'faq_4_q' => ['en' => 'How do I store it?', 'ro' => 'Cum îl păstrez?', 'ru' => 'Как хранить?'],
        'faq_4_a' => ['en' => 'In a cool, dry place. Keep the pack sealed after opening.', 'ro' => 'La loc răcoros și uscat. Ține pachetul închis după deschidere.', 'ru' => 'В прохладном сухом месте. После вскрытия держите упаковку закрытой.'],
        'faq_5_q' => ['en' => 'Allergens?', 'ro' => 'Alergeni?', 'ru' => 'Аллергены?'],
        'faq_5_a' => ['en' => 'Every flavour contains soy. If you have another allergy, write it in the note and Ed will tell you what is safe.', 'ro' => 'Toate aromele conțin soia. Dacă ai altă alergie, scrie-o în notă și Ed îți spune ce poți mânca.', 'ru' => 'Все вкусы содержат сою. Если у вас другая аллергия, напишите об этом в примечании, и Эд подскажет, что вам подходит.'],

        // ---- Order (receipt) ----
        'bar_review' => ['en' => 'Review order', 'ro' => 'Vezi comanda', 'ru' => 'Посмотреть заказ'],
        'sheet_title' => ['en' => 'Your order', 'ro' => 'Comanda ta', 'ru' => 'Ваш заказ'],
        'receipt_sub' => ['en' => 'Homemade jerky · Moldova', 'ro' => 'Jerky de casă · Moldova', 'ru' => 'Домашнее джерки · Молдова'],
        'sheet_close' => ['en' => 'Close', 'ro' => 'Închide', 'ru' => 'Закрыть'],
        'sheet_empty' => ['en' => 'Nothing here yet. Add a flavour from the menu.', 'ro' => 'Încă nimic. Adaugă o aromă din meniu.', 'ru' => 'Пока пусто. Добавьте вкус из меню.'],
        'remove' => ['en' => 'Remove {name}', 'ro' => 'Scoate {name}', 'ru' => 'Убрать {name}'],
        'total' => ['en' => 'Total', 'ro' => 'Total', 'ru' => 'Итого'],
        'details' => ['en' => 'Where can Ed reach you?', 'ro' => 'Unde te poate găsi Ed?', 'ru' => 'Как Эду с вами связаться?'],
        'name' => ['en' => 'Name', 'ro' => 'Nume', 'ru' => 'Имя'],
        'phone' => ['en' => 'Phone', 'ro' => 'Telefon', 'ru' => 'Телефон'],
        'note' => ['en' => 'Note for Ed (optional)', 'ro' => 'Notă pentru Ed (opțional)', 'ru' => 'Примечание для Эда (необязательно)'],
        'note_ph' => ['en' => 'Address, best time to call, anything else', 'ro' => 'Adresa, ora potrivită pentru apel, orice altceva', 'ru' => 'Адрес, удобное время для звонка, что угодно ещё'],
        'send' => ['en' => 'Send order', 'ro' => 'Trimite comanda', 'ru' => 'Отправить заказ'],
        'sending' => ['en' => 'Sending…', 'ro' => 'Se trimite…', 'ru' => 'Отправка…'],
        'sheet_pay' => ['en' => 'You pay on delivery. Ed calls first to confirm.', 'ro' => 'Plătești la livrare. Ed te sună întâi să confirme.', 'ru' => 'Оплата при доставке. Сначала Эд позвонит для подтверждения.'],
        'done_kicker' => ['en' => 'Order received', 'ro' => 'Comandă primită', 'ru' => 'Заказ принят'],
        'done_title' => ['en' => 'Thanks, {name}.', 'ro' => 'Mulțumim, {name}.', 'ru' => 'Спасибо, {name}.'],
        'done_lead' => ['en' => 'Ed has your order and will call you on {phone} to agree the delivery day.', 'ro' => 'Ed a primit comanda și te va suna la {phone} ca să stabiliți ziua livrării.', 'ru' => 'Эд получил ваш заказ и позвонит по номеру {phone}, чтобы договориться о дне доставки.'],
        'done_code' => ['en' => 'Order code', 'ro' => 'Codul comenzii', 'ru' => 'Код заказа'],
        'done_close' => ['en' => 'Done', 'ro' => 'Gata', 'ru' => 'Готово'],

        // ---- Footer ----
        'footer_ask' => ['en' => 'Questions before you order? Ask Ed.', 'ro' => 'Ai întrebări înainte să comanzi? Întreabă-l pe Ed.', 'ru' => 'Есть вопросы до заказа? Спросите Эда.'],
        'footer_ask_empty' => ['en' => 'Questions? Write them in the note when you order and Ed will answer when he calls.', 'ro' => 'Întrebări? Scrie-le în notă când comanzi, iar Ed îți răspunde când te sună.', 'ru' => 'Вопросы? Напишите их в примечании к заказу, и Эд ответит, когда позвонит.'],
        'footer_legal' => ['en' => 'Made by hand in Moldova. Contains soy. Store in a cool, dry place.', 'ro' => 'Făcut manual în Moldova. Conține soia. A se păstra la loc răcoros și uscat.', 'ru' => 'Сделано вручную в Молдове. Содержит сою. Хранить в прохладном сухом месте.'],
        'footer_top' => ['en' => 'Back to the top', 'ro' => 'Înapoi sus', 'ru' => 'Наверх'],
        'footer_photos' => ['en' => 'Food photos: Pexels & Unsplash (serving suggestions).', 'ro' => 'Fotografii: Pexels și Unsplash (sugestii de servire).', 'ru' => 'Фото блюд: Pexels и Unsplash (варианты подачи).'],
    ];

    /** @return array<string, string> key => text in that language (English when there is none). */
    public static function forLocale(string $locale): array
    {
        return array_map(fn (array $t) => $t[$locale] ?? $t['en'], self::ALL);
    }
}
