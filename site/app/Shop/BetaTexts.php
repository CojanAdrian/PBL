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
        'nav_choose' => ['en' => 'Buy', 'ro' => 'Cumpără', 'ru' => 'Купить'],
        'nav_how' => ['en' => 'How it works', 'ro' => 'Cum funcționează', 'ru' => 'Как это работает'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'nav_order' => ['en' => 'Order', 'ro' => 'Comandă', 'ru' => 'Заказ'],

        // ---- Scroll story ----
        'hero_kicker' => ['en' => "Big Ed's Jerky", 'ro' => "Big Ed's Jerky", 'ru' => "Big Ed's Jerky"],
        'hero_title' => ['en' => 'Dried slow. Eaten fast.', 'ro' => 'Uscat încet. Mâncat repede.', 'ru' => 'Сушится долго. Съедается быстро.'],
        'hero_lead' => ['en' => 'Beef and chicken jerky, made by hand in small batches in Moldova.', 'ro' => 'Jerky de vită și de pui, făcut manual, în loturi mici, în Moldova.', 'ru' => 'Джерки из говядины и курицы, сделанное вручную небольшими партиями в Молдове.'],
        'hero_scroll' => ['en' => 'Scroll to see the flavours', 'ro' => 'Derulează pentru arome', 'ru' => 'Листайте, чтобы увидеть вкусы'],
        'beef' => ['en' => 'Beef', 'ro' => 'Vită', 'ru' => 'Говядина'],
        'chicken' => ['en' => 'Chicken', 'ro' => 'Pui', 'ru' => 'Курица'],
        'taster_name' => ['en' => 'Taster box', 'ro' => 'Cutia de degustare', 'ru' => 'Дегустационная коробка'],
        'pack_3d' => ['en' => 'The {name} pack', 'ro' => 'Pachetul {name}', 'ru' => 'Упаковка {name}'],

        // ---- Choose ----
        'choose_title' => ['en' => 'Choose your jerky.', 'ro' => 'Alege-ți jerky-ul.', 'ru' => 'Выберите своё джерки.'],
        'choose_lead' => ['en' => 'Every flavour in beef or chicken, {g} g a pack. You pay when it’s delivered.', 'ro' => 'Fiecare aromă de vită sau de pui, {g} g pachetul. Plătești la livrare.', 'ru' => 'Каждый вкус из говядины или курицы, {g} г в упаковке. Оплата при доставке.'],
        'heat' => ['en' => 'Heat', 'ro' => 'Iuțeală', 'ru' => 'Острота'],
        'heat_1' => ['en' => 'No heat', 'ro' => 'Deloc iute', 'ru' => 'Не острый'],
        'heat_2' => ['en' => 'Mild', 'ro' => 'Puțin iute', 'ru' => 'Слегка острый'],
        'heat_3' => ['en' => 'Medium', 'ro' => 'Mediu', 'ru' => 'Средний'],
        'heat_4' => ['en' => 'Hot', 'ro' => 'Iute', 'ru' => 'Острый'],
        'heat_5' => ['en' => 'Very hot', 'ro' => 'Foarte iute', 'ru' => 'Очень острый'],
        'meat_aria' => ['en' => 'Meat for {name}', 'ro' => 'Carnea pentru {name}', 'ru' => 'Мясо для {name}'],
        'add' => ['en' => 'Add to order', 'ro' => 'Adaugă în comandă', 'ru' => 'Добавить в заказ'],
        'in_order' => ['en' => 'in your order', 'ro' => 'în comandă', 'ru' => 'в заказе'],
        'fewer' => ['en' => 'One fewer {name}', 'ro' => 'Cu unul mai puțin: {name}', 'ru' => 'На одну меньше: {name}'],
        'more' => ['en' => 'One more {name}', 'ro' => 'Încă unul: {name}', 'ru' => 'Ещё одну: {name}'],
        'box_title' => ['en' => 'Can’t choose? Take all six.', 'ro' => 'Nu te poți decide? Ia-le pe toate șase.', 'ru' => 'Не можете выбрать? Берите все шесть.'],

        // ---- How it works ----
        'how_title' => ['en' => 'How it works.', 'ro' => 'Cum funcționează.', 'ru' => 'Как это работает.'],
        'how_1_t' => ['en' => 'You order here.', 'ro' => 'Comanzi aici.', 'ru' => 'Вы заказываете здесь.'],
        'how_1_d' => ['en' => 'Pick your packs, leave your name and phone. No account, no card.', 'ro' => 'Alegi pachetele, lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Выберите упаковки, оставьте имя и телефон. Без регистрации и без карты.'],
        'how_2_t' => ['en' => 'Ed calls you.', 'ro' => 'Ed te sună.', 'ru' => 'Эд вам звонит.'],
        'how_2_d' => ['en' => 'He confirms the order and agrees the delivery day with you.', 'ro' => 'Confirmă comanda și stabilește cu tine ziua livrării.', 'ru' => 'Он подтверждает заказ и договаривается с вами о дне доставки.'],
        'how_3_t' => ['en' => 'You pay on delivery.', 'ro' => 'Plătești la livrare.', 'ru' => 'Оплата при доставке.'],
        'how_3_d' => ['en' => 'Ed brings the jerky to you. You pay when you have it.', 'ro' => 'Ed îți aduce jerky-ul. Plătești când îl primești.', 'ru' => 'Эд привозит джерки. Вы платите, когда получите заказ.'],
        'made_title' => ['en' => 'One kitchen. Small batches.', 'ro' => 'O bucătărie. Loturi mici.', 'ru' => 'Одна кухня. Небольшие партии.'],
        'made_text' => ['en' => 'Ed slices premium beef and chicken thin, marinates each flavour separately and dries it slowly, until it’s chewy and full of flavour. No factory, no shop in between.', 'ro' => 'Ed taie subțire carne premium de vită și de pui, marinează separat fiecare aromă și o usucă încet, până devine elastică și plină de gust. Fără fabrică, fără magazin la mijloc.', 'ru' => 'Эд тонко нарезает отборную говядину и курицу, маринует каждый вкус отдельно и медленно сушит, пока мясо не станет упругим и насыщенным. Без фабрики и без магазина посередине.'],
        'ingredients_title' => ['en' => 'Ingredients', 'ro' => 'Ingrediente', 'ru' => 'Состав'],
        'contains_soy' => ['en' => 'All flavours contain soy.', 'ro' => 'Toate aromele conțin soia.', 'ru' => 'Все вкусы содержат сою.'],

        // ---- FAQ ----
        'faq_title' => ['en' => 'Questions.', 'ro' => 'Întrebări.', 'ru' => 'Вопросы.'],
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
        'sheet_empty' => ['en' => 'Your order is empty.', 'ro' => 'Comanda ta e goală.', 'ru' => 'Ваш заказ пуст.'],
        'remove' => ['en' => 'Remove {name}', 'ro' => 'Scoate {name}', 'ru' => 'Убрать {name}'],
        'total' => ['en' => 'Total', 'ro' => 'Total', 'ru' => 'Итого'],
        'details' => ['en' => 'Where can Ed reach you?', 'ro' => 'Unde te poate găsi Ed?', 'ru' => 'Как Эду с вами связаться?'],
        'name' => ['en' => 'Name', 'ro' => 'Nume', 'ru' => 'Имя'],
        'phone' => ['en' => 'Phone', 'ro' => 'Telefon', 'ru' => 'Телефон'],
        'note' => ['en' => 'Note for Ed (optional)', 'ro' => 'Notă pentru Ed (opțional)', 'ru' => 'Примечание для Эда (необязательно)'],
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
    ];

    /** @return array<string, string> key => text in that language (English when there is none). */
    public static function forLocale(string $locale): array
    {
        return array_map(fn (array $t) => $t[$locale] ?? $t['en'], self::ALL);
    }
}
