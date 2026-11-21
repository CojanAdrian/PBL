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
        'nav_how' => ['en' => 'How to order', 'ro' => 'Cum comanzi', 'ru' => 'Как заказать'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'nav_order' => ['en' => 'Order', 'ro' => 'Comandă', 'ru' => 'Заказ'],

        // ---- Hero ----
        'hero_kicker' => ['en' => 'Homemade jerky · Moldova', 'ro' => 'Jerky de casă · Moldova', 'ru' => 'Домашнее джерки · Молдова'],
        'hero_title_1' => ['en' => 'Dried slow.', 'ro' => 'Uscat încet.', 'ru' => 'Сушится долго.'],
        'hero_title_2' => ['en' => 'Eaten fast.', 'ro' => 'Mâncat repede.', 'ru' => 'Съедается быстро.'],
        'hero_lead' => ['en' => 'Beef and chicken jerky, made by hand in small batches in Ed’s kitchen. Three flavours. Order here, pay when it’s delivered.', 'ro' => 'Jerky de vită și de pui, făcut manual, în loturi mici, în bucătăria lui Ed. Trei arome. Comanzi aici, plătești la livrare.', 'ru' => 'Джерки из говядины и курицы, сделанное вручную небольшими партиями на кухне Эда. Три вкуса. Заказ здесь, оплата при доставке.'],
        'hero_cta' => ['en' => 'Pick a flavour', 'ro' => 'Alege o aromă', 'ru' => 'Выбрать вкус'],
        'hero_from' => ['en' => 'from {price}', 'ro' => 'de la {price}', 'ru' => 'от {price}'],
        'hero_pay' => ['en' => 'pay on delivery', 'ro' => 'plată la livrare', 'ru' => 'оплата при доставке'],
        'hint_drag' => ['en' => 'Drag to spin', 'ro' => 'Trage ca să rotești', 'ru' => 'Потяните, чтобы вращать'],
        'hint_tap' => ['en' => 'Tap the pack', 'ro' => 'Atinge pachetul', 'ru' => 'Нажмите на упаковку'],

        // ---- New to jerky ----
        'new_kicker' => ['en' => 'New to jerky?', 'ro' => 'Nu ai mai gustat jerky?', 'ru' => 'Впервые пробуете джерки?'],
        'new_title' => ['en' => 'Meat, sliced thin, marinated and dried slowly.', 'ro' => 'Carne tăiată subțire, marinată și uscată încet.', 'ru' => 'Мясо, тонко нарезанное, замаринованное и медленно высушенное.'],
        'new_1_t' => ['en' => 'A snack, not a meal', 'ro' => 'O gustare, nu o masă', 'ru' => 'Закуска, а не обед'],
        'new_1_d' => ['en' => 'Chewy, savoury strips you eat straight from the pack.', 'ro' => 'Fâșii elastice și gustoase, mâncate direct din pachet.', 'ru' => 'Упругие, пряные полоски, которые едят прямо из упаковки.'],
        'new_2_t' => ['en' => 'Goes anywhere', 'ro' => 'Merge oriunde', 'ru' => 'С собой куда угодно'],
        'new_2_d' => ['en' => 'On the road, at work, after the gym, with a beer. No fridge needed.', 'ro' => 'La drum, la birou, după sală, la o bere. Fără frigider.', 'ru' => 'В дороге, на работе, после зала, к пиву. Холодильник не нужен.'],
        'new_3_t' => ['en' => 'Made by Ed', 'ro' => 'Făcut de Ed', 'ru' => 'Сделано Эдом'],
        'new_3_d' => ['en' => 'One kitchen in Moldova, premium beef and chicken, small batches.', 'ro' => 'O bucătărie în Moldova, carne premium de vită și de pui, loturi mici.', 'ru' => 'Одна кухня в Молдове, отборная говядина и курица, небольшие партии.'],

        // ---- Flavours ----
        'lab_kicker' => ['en' => 'The flavours', 'ro' => 'Aromele', 'ru' => 'Вкусы'],
        'lab_lead' => ['en' => 'Every flavour in beef or chicken, {g} g a pack.', 'ro' => 'Fiecare aromă de vită sau de pui, {g} g pachetul.', 'ru' => 'Каждый вкус из говядины или курицы, {g} г в упаковке.'],
        'lab_noscript' => ['en' => 'The menu needs JavaScript. Please turn it on, or contact Ed directly.', 'ro' => 'Meniul are nevoie de JavaScript. Activează-l sau contactează-l direct pe Ed.', 'ru' => 'Для меню нужен JavaScript. Включите его или свяжитесь с Эдом напрямую.'],
        'taster_name' => ['en' => 'All six', 'ro' => 'Toate șase', 'ru' => 'Все шесть'],
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
        'pack_3d' => ['en' => '3D view of the {name} pack', 'ro' => 'Pachetul {name} în 3D', 'ru' => 'Упаковка {name} в 3D'],
        'pack_ingredients' => ['en' => 'Ingredients', 'ro' => 'Ingrediente', 'ru' => 'Состав'],
        'pack_made' => ['en' => 'Made by hand in Moldova', 'ro' => 'Făcut manual în Moldova', 'ru' => 'Сделано вручную в Молдове'],

        // ---- How to order ----
        'how_title' => ['en' => 'How to order', 'ro' => 'Cum comanzi', 'ru' => 'Как заказать'],
        'how_1_t' => ['en' => 'Pick your packs', 'ro' => 'Alegi pachetele', 'ru' => 'Выберите упаковки'],
        'how_1_d' => ['en' => 'Add flavours, then leave your name and phone. No account, no card.', 'ro' => 'Adaugi arome, apoi lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Добавьте вкусы и оставьте имя и телефон. Без регистрации и без карты.'],
        'how_2_t' => ['en' => 'Ed calls you', 'ro' => 'Ed te sună', 'ru' => 'Эд вам звонит'],
        'how_2_d' => ['en' => 'He confirms the order and agrees the delivery day with you.', 'ro' => 'Confirmă comanda și stabilește cu tine ziua livrării.', 'ru' => 'Он подтверждает заказ и договаривается с вами о дне доставки.'],
        'how_3_t' => ['en' => 'Pay on delivery', 'ro' => 'Plătești la livrare', 'ru' => 'Оплата при доставке'],
        'how_3_d' => ['en' => 'Ed brings the jerky to you, and you pay when you have it.', 'ro' => 'Ed îți aduce jerky-ul, iar tu plătești când îl primești.', 'ru' => 'Эд привозит джерки, и вы платите, когда получите заказ.'],
        'ingredients_title' => ['en' => 'What goes in', 'ro' => 'Ce conține', 'ru' => 'Состав'],
        'contains_soy' => ['en' => 'All flavours contain soy.', 'ro' => 'Toate aromele conțin soia.', 'ru' => 'Все вкусы содержат сою.'],

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

        // ---- Order panel ----
        'bar_review' => ['en' => 'Review order', 'ro' => 'Vezi comanda', 'ru' => 'Посмотреть заказ'],
        'sheet_title' => ['en' => 'Your order', 'ro' => 'Comanda ta', 'ru' => 'Ваш заказ'],
        'sheet_close' => ['en' => 'Close', 'ro' => 'Închide', 'ru' => 'Закрыть'],
        'sheet_empty' => ['en' => 'Nothing here yet. Pick a flavour first.', 'ro' => 'Încă nimic. Alege mai întâi o aromă.', 'ru' => 'Пока пусто. Сначала выберите вкус.'],
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
        'done_title' => ['en' => 'Thanks, {name}.', 'ro' => 'Mulțumim, {name}.', 'ru' => 'Спасибо, {name}.'],
        'done_lead' => ['en' => 'Ed has your order and will call you on {phone} to agree the delivery day.', 'ro' => 'Ed a primit comanda și te va suna la {phone} ca să stabiliți ziua livrării.', 'ru' => 'Эд получил ваш заказ и позвонит по номеру {phone}, чтобы договориться о дне доставки.'],
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
