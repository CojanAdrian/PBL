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

        // ---- Loader, nav, clock ----
        'loading' => ['en' => 'Firing up the kitchen', 'ro' => 'Se aprinde bucătăria', 'ru' => 'Разжигаем кухню'],
        'nav_flavours' => ['en' => 'Flavours', 'ro' => 'Arome', 'ru' => 'Вкусы'],
        'nav_how' => ['en' => 'How to order', 'ro' => 'Cum comanzi', 'ru' => 'Как заказать'],
        'nav_story' => ['en' => 'Ed', 'ro' => 'Ed', 'ru' => 'Эд'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'pull' => ['en' => 'Pull me', 'ro' => 'Trage', 'ru' => 'Потяни'],
        'pull_order' => ['en' => 'Your order', 'ro' => 'Comanda ta', 'ru' => 'Ваш заказ'],
        'clock_place' => ['en' => 'Ed’s kitchen, Moldova', 'ro' => 'Bucătăria lui Ed, Moldova', 'ru' => 'Кухня Эда, Молдова'],
        'clock_open' => ['en' => 'Taking orders', 'ro' => 'Primim comenzi', 'ru' => 'Принимаем заказы'],

        // ---- Hero ----
        'hero_kicker' => ['en' => 'Beef & chicken jerky', 'ro' => 'Jerky de vită și de pui', 'ru' => 'Джерки из говядины и курицы'],
        'hero_line' => ['en' => 'Made by hand, after dark, in one kitchen in Moldova.', 'ro' => 'Făcut manual, noaptea târziu, într-o singură bucătărie din Moldova.', 'ru' => 'Сделано вручную, поздно ночью, на одной кухне в Молдове.'],
        'hero_cta' => ['en' => 'Pick your flavour', 'ro' => 'Alege-ți aroma', 'ru' => 'Выбрать вкус'],
        'hero_from' => ['en' => 'from {price} · pay at the door', 'ro' => 'de la {price} · plătești la ușă', 'ru' => 'от {price} · оплата при получении'],

        // ---- Tapes ----
        'tape_1' => ['en' => 'Handmade in Moldova', 'ro' => 'Făcut manual în Moldova', 'ru' => 'Ручная работа, Молдова'],
        'tape_2' => ['en' => 'Small batch', 'ro' => 'Lot mic', 'ru' => 'Малая партия'],
        'tape_3' => ['en' => 'Pay at the door', 'ro' => 'Plătești la ușă', 'ru' => 'Оплата при получении'],
        'tape_4' => ['en' => 'No shortcuts', 'ro' => 'Fără scurtături', 'ru' => 'Без компромиссов'],

        // ---- How to order ----
        'how_kicker' => ['en' => 'Ordering takes a minute', 'ro' => 'Comanda durează un minut', 'ru' => 'Заказ за минуту'],
        'how_1' => ['en' => 'Pick', 'ro' => 'Alegi', 'ru' => 'Выбери'],
        'how_1_d' => ['en' => 'Tap + on a flavour. Beef or chicken, as many packs as you want.', 'ro' => 'Apeși + la o aromă. Vită sau pui, câte pachete vrei.', 'ru' => 'Нажми + у вкуса. Говядина или курица, сколько угодно упаковок.'],
        'how_2' => ['en' => 'Send', 'ro' => 'Trimiți', 'ru' => 'Отправь'],
        'how_2_d' => ['en' => 'Open your order, leave your name and phone. No account, no card.', 'ro' => 'Deschizi comanda, lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Откройте заказ, оставьте имя и телефон. Без регистрации и карты.'],
        'how_3' => ['en' => 'Eat', 'ro' => 'Mănânci', 'ru' => 'Ешь'],
        'how_3_d' => ['en' => 'Ed calls to set the day, brings it to you, and you pay at the door.', 'ro' => 'Ed te sună pentru zi, ți-l aduce, iar plătești la ușă.', 'ru' => 'Эд позвонит, договорится о дне, привезёт, и ты заплатишь при получении.'],

        // ---- Flavours ----
        'flav_kicker' => ['en' => 'The flavours', 'ro' => 'Aromele', 'ru' => 'Вкусы'],
        'flav_title' => ['en' => 'Three flavours. Two meats.', 'ro' => 'Trei arome. Două cărnuri.', 'ru' => 'Три вкуса. Два вида мяса.'],
        'flav_lead' => ['en' => 'Every pack is {g} g. Tap + to add it to your order.', 'ro' => 'Fiecare pachet are {g} g. Apasă + ca să-l adaugi în comandă.', 'ru' => 'В каждой упаковке {g} г. Нажми +, чтобы добавить в заказ.'],
        'flavour_texas_word' => ['en' => 'Smoke', 'ro' => 'Fum', 'ru' => 'Дым'],
        'flavour_garlic_word' => ['en' => 'Garlic', 'ro' => 'Usturoi', 'ru' => 'Чеснок'],
        'flavour_honey_word' => ['en' => 'Fire', 'ro' => 'Foc', 'ru' => 'Огонь'],
        'taster_word' => ['en' => 'All six', 'ro' => 'Toate șase', 'ru' => 'Все шесть'],
        'heat' => ['en' => 'Heat', 'ro' => 'Iuțeală', 'ru' => 'Острота'],
        'heat_1' => ['en' => 'No heat', 'ro' => 'Deloc iute', 'ru' => 'Не острый'],
        'heat_2' => ['en' => 'Mild', 'ro' => 'Puțin iute', 'ru' => 'Слегка острый'],
        'heat_3' => ['en' => 'Medium', 'ro' => 'Mediu', 'ru' => 'Средний'],
        'heat_4' => ['en' => 'Hot', 'ro' => 'Iute', 'ru' => 'Острый'],
        'heat_5' => ['en' => 'Very hot', 'ro' => 'Foarte iute', 'ru' => 'Очень острый'],
        'add_meat' => ['en' => 'Add {meat}', 'ro' => 'Adaugă {meat}', 'ru' => 'Добавить: {meat}'],
        'in_order' => ['en' => 'in order', 'ro' => 'în comandă', 'ru' => 'в заказе'],
        'fewer' => ['en' => 'One fewer {name}', 'ro' => 'Cu unul mai puțin: {name}', 'ru' => 'На одну меньше: {name}'],
        'more' => ['en' => 'One more {name}', 'ro' => 'Încă unul: {name}', 'ru' => 'Ещё одну: {name}'],
        'pack_alt' => ['en' => 'The {name} pack', 'ro' => 'Pachetul {name}', 'ru' => 'Упаковка {name}'],
        'taster_lead' => ['en' => 'Can’t decide? A 50 g pack of every flavour, beef and chicken.', 'ro' => 'Nu te poți decide? Câte un pachet de 50 g din fiecare aromă, vită și pui.', 'ru' => 'Не можете выбрать? По упаковке 50 г каждого вкуса, говядина и курица.'],
        'add' => ['en' => 'Add', 'ro' => 'Adaugă', 'ru' => 'Добавить'],

        // ---- Inspector ----
        'inspect_kicker' => ['en' => 'Turn it over', 'ro' => 'Întoarce-l', 'ru' => 'Переверни'],
        'inspect_title' => ['en' => 'Nothing to hide.', 'ro' => 'Nimic de ascuns.', 'ru' => 'Нечего скрывать.'],
        'inspect_lead' => ['en' => 'Drag the pack to turn it, or pick a side. The back lists everything that goes in.', 'ro' => 'Trage pachetul ca să-l rotești sau alege o parte. Pe spate scrie tot ce conține.', 'ru' => 'Потяни упаковку, чтобы повернуть, или выбери сторону. На обороте написан весь состав.'],
        'face_front' => ['en' => 'Front', 'ro' => 'Față', 'ru' => 'Лицо'],
        'face_side' => ['en' => 'Side', 'ro' => 'Lateral', 'ru' => 'Бок'],
        'face_back' => ['en' => 'Back', 'ro' => 'Spate', 'ru' => 'Оборот'],
        'reset' => ['en' => 'Reset', 'ro' => 'Resetează', 'ru' => 'Сброс'],
        'ingredients_title' => ['en' => 'Ingredients', 'ro' => 'Ingrediente', 'ru' => 'Состав'],
        'contains_soy' => ['en' => 'All flavours contain soy.', 'ro' => 'Toate aromele conțin soia.', 'ru' => 'Все вкусы содержат сою.'],
        'store' => ['en' => 'Keep in a cool, dry place.', 'ro' => 'A se păstra la loc răcoros și uscat.', 'ru' => 'Хранить в прохладном сухом месте.'],
        'made' => ['en' => 'Made by hand in Moldova', 'ro' => 'Făcut manual în Moldova', 'ru' => 'Сделано вручную в Молдове'],

        // ---- Ed ----
        'story_kicker' => ['en' => 'Who is Ed', 'ro' => 'Cine e Ed', 'ru' => 'Кто такой Эд'],
        'story_title_1' => ['en' => 'Some call it a snack.', 'ro' => 'Unii îi zic gustare.', 'ru' => 'Кто-то называет это закуской.'],
        'story_title_2' => ['en' => 'Ed calls it a craft.', 'ro' => 'Ed îi zice meserie.', 'ru' => 'Эд называет это ремеслом.'],
        'story_text' => ['en' => 'No factory. One kitchen, premium beef and chicken, and nights of slicing, marinating and slow drying. The man who makes your jerky is the man who calls you and brings it to your door.', 'ro' => 'Nicio fabrică. O bucătărie, carne premium de vită și de pui și nopți întregi de tăiat, marinat și uscat încet. Cel care îți face jerky-ul e cel care te sună și ți-l aduce la ușă.', 'ru' => 'Никакой фабрики. Одна кухня, отборная говядина и курица и ночи нарезки, маринада и медленной сушки. Тот, кто делает ваше джерки, сам звонит вам и привозит его к двери.'],
        'fact_1_t' => ['en' => 'Sliced by hand', 'ro' => 'Tăiat manual', 'ru' => 'Нарезано вручную'],
        'fact_1_d' => ['en' => 'Lean cuts, thin strips, one by one.', 'ro' => 'Bucăți slabe, fâșii subțiri, una câte una.', 'ru' => 'Нежирное мясо, тонкие полоски, одна за другой.'],
        'fact_2_t' => ['en' => 'Its own marinade', 'ro' => 'Marinadă proprie', 'ru' => 'Свой маринад'],
        'fact_2_d' => ['en' => 'Every flavour is marinated separately.', 'ro' => 'Fiecare aromă e marinată separat.', 'ru' => 'Каждый вкус маринуется отдельно.'],
        'fact_3_t' => ['en' => 'Dried slow', 'ro' => 'Uscat încet', 'ru' => 'Медленная сушка'],
        'fact_3_d' => ['en' => 'Hours, not minutes. Chewy, not hard.', 'ro' => 'Ore, nu minute. Elastic, nu tare.', 'ru' => 'Часы, а не минуты. Упругое, а не жёсткое.'],
        'fact_4_t' => ['en' => 'Brought by Ed', 'ro' => 'Adus de Ed', 'ru' => 'Привозит Эд'],
        'fact_4_d' => ['en' => 'He delivers it himself. You pay at the door.', 'ro' => 'Îl livrează chiar el. Plătești la ușă.', 'ru' => 'Он привозит сам. Оплата при получении.'],

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

        // ---- Order drawer ----
        'sheet_title' => ['en' => 'Your order', 'ro' => 'Comanda ta', 'ru' => 'Ваш заказ'],
        'sheet_close' => ['en' => 'Close', 'ro' => 'Închide', 'ru' => 'Закрыть'],
        'sheet_empty' => ['en' => 'Empty for now. Tap + on a flavour.', 'ro' => 'Deocamdată goală. Apasă + la o aromă.', 'ru' => 'Пока пусто. Нажмите + у вкуса.'],
        'sheet_browse' => ['en' => 'See the flavours', 'ro' => 'Vezi aromele', 'ru' => 'Смотреть вкусы'],
        'remove' => ['en' => 'Remove', 'ro' => 'Scoate', 'ru' => 'Убрать'],
        'total' => ['en' => 'Total', 'ro' => 'Total', 'ru' => 'Итого'],
        'details' => ['en' => 'Where can Ed reach you?', 'ro' => 'Unde te poate găsi Ed?', 'ru' => 'Как Эду с вами связаться?'],
        'name' => ['en' => 'Name', 'ro' => 'Nume', 'ru' => 'Имя'],
        'phone' => ['en' => 'Phone', 'ro' => 'Telefon', 'ru' => 'Телефон'],
        'note' => ['en' => 'Note for Ed (optional)', 'ro' => 'Notă pentru Ed (opțional)', 'ru' => 'Примечание для Эда (необязательно)'],
        'send' => ['en' => 'Send order', 'ro' => 'Trimite comanda', 'ru' => 'Отправить заказ'],
        'sending' => ['en' => 'Sending…', 'ro' => 'Se trimite…', 'ru' => 'Отправка…'],
        'sheet_pay' => ['en' => 'No payment now. Ed calls to confirm, you pay at the door.', 'ro' => 'Nu plătești acum. Ed te sună să confirme, plătești la ușă.', 'ru' => 'Сейчас ничего не платите. Эд позвонит, оплата при получении.'],
        'done_title' => ['en' => 'It’s with Ed, {name}.', 'ro' => 'A ajuns la Ed, {name}.', 'ru' => 'Заказ у Эда, {name}.'],
        'done_lead' => ['en' => 'He will call you on {phone} to agree the delivery day.', 'ro' => 'Te va suna la {phone} ca să stabiliți ziua livrării.', 'ru' => 'Он позвонит по номеру {phone}, чтобы договориться о дне доставки.'],
        'done_code' => ['en' => 'Order code', 'ro' => 'Codul comenzii', 'ru' => 'Код заказа'],
        'done_close' => ['en' => 'Done', 'ro' => 'Gata', 'ru' => 'Готово'],

        // ---- Footer ----
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
