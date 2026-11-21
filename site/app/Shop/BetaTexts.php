<?php

namespace App\Shop;

/**
 * Texts for the redesigned page at /{locale}/betatest (English, Romanian, Russian).
 * Kept apart from Text blocks while the design is being tested; once it replaces
 * the main page they can move into Texts.php so they become editable in the admin.
 * Order-form errors, plurals and the currency still come from the shop_… Text blocks.
 */
class BetaTexts
{
    public const ALL = [
        'title' => ['en' => "Big Ed's Jerky · homemade beef & chicken jerky from Moldova", 'ro' => "Big Ed's Jerky · jerky de casă din vită și pui, din Moldova", 'ru' => "Big Ed's Jerky · домашнее джерки из говядины и курицы, Молдова"],
        'description' => ['en' => 'Beef and chicken jerky made by hand in small batches in Moldova. Three flavours. Order online, pay on delivery.', 'ro' => 'Jerky de vită și de pui făcut manual, în loturi mici, în Moldova. Trei arome. Comanzi online, plătești la livrare.', 'ru' => 'Джерки из говядины и курицы, сделанное вручную небольшими партиями в Молдове. Три вкуса. Заказ онлайн, оплата при доставке.'],

        'beta_bar' => ['en' => 'New design (beta).', 'ro' => 'Design nou (beta).', 'ru' => 'Новый дизайн (бета).'],
        'beta_back' => ['en' => 'Back to the current site', 'ro' => 'Înapoi la site-ul actual', 'ru' => 'Вернуться на текущий сайт'],

        'nav_flavours' => ['en' => 'Flavours', 'ro' => 'Arome', 'ru' => 'Вкусы'],
        'nav_how' => ['en' => 'How it works', 'ro' => 'Cum funcționează', 'ru' => 'Как это работает'],
        'nav_about' => ['en' => 'About Ed', 'ro' => 'Despre Ed', 'ru' => 'Об Эде'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'nav_order' => ['en' => 'Order', 'ro' => 'Comandă', 'ru' => 'Заказ'],

        'hero_kicker' => ['en' => 'Homemade jerky · Moldova', 'ro' => 'Jerky de casă · Moldova', 'ru' => 'Домашнее джерки · Молдова'],
        'hero_title_1' => ['en' => 'Dried slow.', 'ro' => 'Uscat încet.', 'ru' => 'Сушится долго.'],
        'hero_title_2' => ['en' => 'Eaten fast.', 'ro' => 'Mâncat repede.', 'ru' => 'Съедается быстро.'],
        'hero_lead' => ['en' => "Beef and chicken jerky Ed makes by hand in his kitchen in Moldova. Three flavours, small batches, and you pay when it's delivered.", 'ro' => 'Jerky de vită și de pui pe care Ed îl face manual, în bucătăria lui din Moldova. Trei arome, loturi mici, iar plătești la livrare.', 'ru' => 'Джерки из говядины и курицы, которое Эд делает вручную на своей кухне в Молдове. Три вкуса, небольшие партии, оплата при доставке.'],
        'hero_cta' => ['en' => 'Pick your flavour', 'ro' => 'Alege-ți aroma', 'ru' => 'Выбрать вкус'],
        'hero_from' => ['en' => 'from {price}', 'ro' => 'de la {price}', 'ru' => 'от {price}'],
        'hero_pay' => ['en' => 'pay on delivery', 'ro' => 'plată la livrare', 'ru' => 'оплата при доставке'],
        'hero_hint' => ['en' => 'Move your mouse to clear the smoke', 'ro' => 'Mișcă mouse-ul ca să risipești fumul', 'ru' => 'Двигайте мышью, чтобы развеять дым'],
        'hero_hint_touch' => ['en' => 'Touch the smoke to clear it', 'ro' => 'Atinge fumul ca să-l risipești', 'ru' => 'Коснитесь дыма, чтобы развеять его'],
        'hero_photo_alt' => ['en' => 'Spice-crusted strips of jerky on a wooden board next to garlic', 'ro' => 'Fâșii de jerky cu condimente pe un fund de lemn, lângă usturoi', 'ru' => 'Полоски джерки в специях на деревянной доске рядом с чесноком'],

        'flav_title_1' => ['en' => 'Three flavours.', 'ro' => 'Trei arome.', 'ru' => 'Три вкуса.'],
        'flav_title_2' => ['en' => 'Beef or chicken.', 'ro' => 'Vită sau pui.', 'ru' => 'Говядина или курица.'],
        'flav_lead' => ['en' => 'Every pack is {g} g. Pick a flavour, choose the meat, and add as many as you like.', 'ro' => 'Fiecare pachet are {g} g. Alege aroma, alege carnea și adaugă câte vrei.', 'ru' => 'В каждой упаковке {g} г. Выберите вкус и мясо и добавьте сколько хотите.'],
        'menu_noscript' => ['en' => 'The menu needs JavaScript. Please turn it on, or contact Ed directly.', 'ro' => 'Meniul are nevoie de JavaScript. Activează-l sau contactează-l direct pe Ed.', 'ru' => 'Для меню нужен JavaScript. Включите его или свяжитесь с Эдом напрямую.'],
        'heat' => ['en' => 'Heat', 'ro' => 'Iuțeală', 'ru' => 'Острота'],
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
        'turn' => ['en' => 'Turn the pack', 'ro' => 'Întoarce pachetul', 'ru' => 'Перевернуть упаковку'],
        'turn_back' => ['en' => 'Front of the pack', 'ro' => 'Fața pachetului', 'ru' => 'Лицевая сторона'],
        'pack_ingredients' => ['en' => 'Ingredients', 'ro' => 'Ingrediente', 'ru' => 'Состав'],
        'pack_made' => ['en' => 'Made by hand in Moldova', 'ro' => 'Făcut manual în Moldova', 'ru' => 'Сделано вручную в Молдове'],
        'pack_store' => ['en' => 'Keep in a cool, dry place.', 'ro' => 'A se păstra la loc răcoros și uscat.', 'ru' => 'Хранить в прохладном сухом месте.'],

        'crate_kicker' => ['en' => "Can't choose?", 'ro' => 'Nu te poți decide?', 'ru' => 'Не можете выбрать?'],
        'crate_title' => ['en' => 'Take all six.', 'ro' => 'Ia-le pe toate șase.', 'ru' => 'Берите все шесть.'],

        'how_title' => ['en' => 'How it works', 'ro' => 'Cum funcționează', 'ru' => 'Как это работает'],
        'how_1_t' => ['en' => 'You order here', 'ro' => 'Comanzi aici', 'ru' => 'Вы заказываете здесь'],
        'how_1_d' => ['en' => 'Pick your packs, then leave your name and phone. No account, no card.', 'ro' => 'Alegi pachetele, apoi lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Выберите упаковки и оставьте имя и телефон. Без регистрации и без карты.'],
        'how_2_t' => ['en' => 'Ed calls you', 'ro' => 'Ed te sună', 'ru' => 'Эд вам звонит'],
        'how_2_d' => ['en' => 'He confirms the order and agrees the delivery day with you.', 'ro' => 'Confirmă comanda și stabilește cu tine ziua livrării.', 'ru' => 'Он подтверждает заказ и договаривается с вами о дне доставки.'],
        'how_3_t' => ['en' => 'You pay on delivery', 'ro' => 'Plătești la livrare', 'ru' => 'Оплата при доставке'],
        'how_3_d' => ['en' => 'Ed brings the jerky to you, and you pay when you have it.', 'ro' => 'Ed îți aduce jerky-ul, iar tu plătești când îl primești.', 'ru' => 'Эд привозит джерки, и вы платите, когда получите заказ.'],
        'how_stamp' => ['en' => 'Nothing to pay online', 'ro' => 'Nimic de plătit online', 'ru' => 'Ничего не платите онлайн'],

        'about_kicker' => ['en' => 'About Ed', 'ro' => 'Despre Ed', 'ru' => 'Об Эде'],
        'about_title' => ['en' => 'One kitchen. One Ed.', 'ro' => 'O bucătărie. Un Ed.', 'ru' => 'Одна кухня. Один Эд.'],
        'about_1' => ['en' => "Big Ed's is made by hand in Ed's home kitchen in Moldova, one small batch at a time, from premium beef and chicken.", 'ro' => "Big Ed's e făcut manual în bucătăria lui Ed din Moldova, câte un lot mic o dată, din carne premium de vită și de pui.", 'ru' => "Big Ed's делается вручную на домашней кухне Эда в Молдове, небольшими партиями, из отборной говядины и курицы."],
        'about_2' => ['en' => 'There is no shop in between. The person who makes your jerky is the one who calls you about your order and brings it to your door.', 'ro' => 'Nu există niciun magazin la mijloc. Cel care îți face jerky-ul e același care te sună despre comandă și ți-o aduce la ușă.', 'ru' => 'Никаких посредников. Тот, кто делает ваше джерки, сам звонит вам по заказу и привозит его к двери.'],
        'ingredients_title' => ['en' => 'What goes in', 'ro' => 'Ce conține', 'ru' => 'Состав'],
        'contains_soy' => ['en' => 'All flavours contain soy.', 'ro' => 'Toate aromele conțin soia.', 'ru' => 'Все вкусы содержат сою.'],

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
        'sending' => ['en' => 'Printing…', 'ro' => 'Se tipărește…', 'ru' => 'Печатаем…'],
        'sheet_pay' => ['en' => 'You pay on delivery. Ed calls first to confirm.', 'ro' => 'Plătești la livrare. Ed te sună întâi să confirme.', 'ru' => 'Оплата при доставке. Сначала Эд позвонит для подтверждения.'],
        'done_kicker' => ['en' => 'Order received', 'ro' => 'Comandă primită', 'ru' => 'Заказ принят'],
        'done_title' => ['en' => 'Thanks, {name}.', 'ro' => 'Mulțumim, {name}.', 'ru' => 'Спасибо, {name}.'],
        'done_lead' => ['en' => 'Ed has your order and will call you on {phone} to agree the delivery day.', 'ro' => 'Ed a primit comanda și te va suna la {phone} ca să stabiliți ziua livrării.', 'ru' => 'Эд получил ваш заказ и позвонит по номеру {phone}, чтобы договориться о дне доставки.'],
        'done_code' => ['en' => 'Order code', 'ro' => 'Codul comenzii', 'ru' => 'Код заказа'],
        'done_close' => ['en' => 'Done', 'ro' => 'Gata', 'ru' => 'Готово'],

        'footer_ask' => ['en' => 'Questions before you order? Ask Ed.', 'ro' => 'Ai întrebări înainte să comanzi? Întreabă-l pe Ed.', 'ru' => 'Есть вопросы до заказа? Спросите Эда.'],
        'footer_ask_empty' => ['en' => 'Questions? Write them in the note when you order and Ed will answer when he calls.', 'ro' => 'Întrebări? Scrie-le în notă când comanzi, iar Ed îți răspunde când te sună.', 'ru' => 'Вопросы? Напишите их в примечании к заказу, и Эд ответит, когда позвонит.'],
        'footer_legal' => ['en' => 'Made by hand in Moldova. Contains soy. Store in a cool, dry place.', 'ro' => 'Făcut manual în Moldova. Conține soia. A se păstra la loc răcoros și uscat.', 'ru' => 'Сделано вручную в Молдове. Содержит сою. Хранить в прохладном сухом месте.'],
        'footer_top' => ['en' => 'Back to the top', 'ro' => 'Înapoi sus', 'ru' => 'Наверх'],
    ];

    /** @return array<string, string> key => text in that language (English when there is none). */
    public static function forLocale(string $locale): array
    {
        return array_map(fn (array $t) => $t[$locale] ?? $t['en'], self::ALL);
    }
}
