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

        // ---- Loader, nav, language, cart ----
        'loading' => ['en' => 'Firing up the kitchen', 'ro' => 'Se aprinde bucătăria', 'ru' => 'Разжигаем кухню'],
        'nav_shop' => ['en' => 'Order', 'ro' => 'Comandă', 'ru' => 'Заказать'],
        'nav_flavours' => ['en' => 'Flavours', 'ro' => 'Arome', 'ru' => 'Вкусы'],
        'nav_how' => ['en' => 'How it works', 'ro' => 'Cum funcționează', 'ru' => 'Как это работает'],
        'nav_story' => ['en' => 'Who’s Ed', 'ro' => 'Cine e Ed', 'ru' => 'Кто такой Эд'],
        'nav_faq' => ['en' => 'Questions', 'ro' => 'Întrebări', 'ru' => 'Вопросы'],
        'nav_menu' => ['en' => 'Menu', 'ro' => 'Meniu', 'ru' => 'Меню'],
        'lang_label' => ['en' => 'Language', 'ro' => 'Limba', 'ru' => 'Язык'],
        'cart' => ['en' => 'Cart', 'ro' => 'Coș', 'ru' => 'Корзина'],
        'cart_empty' => ['en' => 'Empty', 'ro' => 'Gol', 'ru' => 'Пусто'],
        'cart_open' => ['en' => 'Open your cart', 'ro' => 'Deschide coșul', 'ru' => 'Открыть корзину'],
        'checkout' => ['en' => 'Checkout', 'ro' => 'Finalizează', 'ru' => 'Оформить'],

        // ---- Hero ----
        'hero_kicker' => ['en' => 'Beef & chicken jerky', 'ro' => 'Jerky de vită și de pui', 'ru' => 'Джерки из говядины и курицы'],
        'hero_line' => ['en' => 'Made by hand, after dark, in one kitchen in Moldova.', 'ro' => 'Făcut manual, noaptea târziu, într-o singură bucătărie din Moldova.', 'ru' => 'Сделано вручную, поздно ночью, на одной кухне в Молдове.'],
        'hero_cta' => ['en' => 'Click to order', 'ro' => 'Comandă acum', 'ru' => 'Заказать'],
        'hero_box_badge' => ['en' => 'Order the taster box', 'ro' => 'Comandă cutia de degustare', 'ru' => 'Закажи дегустационный набор'],
        'hero_box_sub' => ['en' => 'All 6 flavours · {price}', 'ro' => 'Toate cele 6 arome · {price}', 'ru' => 'Все 6 вкусов · {price}'],
        'hero_box_hint' => ['en' => 'Hover to open · Click to order', 'ro' => 'Treci cu mouse-ul ca s-o deschizi · Click ca s-o comanzi', 'ru' => 'Наведите, чтобы открыть · Нажмите, чтобы заказать'],
        'hero_box_hint_touch' => ['en' => 'Tap to add it to your cart', 'ro' => 'Atinge ca s-o adaugi în coș', 'ru' => 'Нажмите, чтобы добавить в корзину'],
        'hero_box_aria' => ['en' => 'Add the taster box to your cart and go to the order section', 'ro' => 'Adaugă cutia de degustare în coș și mergi la comandă', 'ru' => 'Добавить дегустационный набор в корзину и перейти к заказу'],
        'hero_box_added' => ['en' => '{name} is in your cart.', 'ro' => '{name} e în coș.', 'ru' => '{name} в корзине.'],
        'hero_note' => ['en' => 'From {price} · Pay at the door', 'ro' => 'De la {price} · Plătești la ușă', 'ru' => 'От {price} · Оплата при получении'],

        // ---- Tapes ----
        'tape_1' => ['en' => 'Handmade in Moldova', 'ro' => 'Făcut manual în Moldova', 'ru' => 'Ручная работа, Молдова'],
        'tape_2' => ['en' => 'Small batch', 'ro' => 'Lot mic', 'ru' => 'Малая партия'],
        'tape_3' => ['en' => 'Pay at the door', 'ro' => 'Plătești la ușă', 'ru' => 'Оплата при получении'],
        'tape_4' => ['en' => 'No shortcuts', 'ro' => 'Fără scurtături', 'ru' => 'Без компромиссов'],

        // ---- How to order ----
        'how_kicker' => ['en' => 'Ordering takes a minute', 'ro' => 'Comanda durează un minut', 'ru' => 'Заказ за минуту'],
        'how_1' => ['en' => 'Pick', 'ro' => 'Alegi', 'ru' => 'Выбери'],
        'how_1_d' => ['en' => 'Choose beef or chicken and tap Add on the flavours you want.', 'ro' => 'Alegi vită sau pui și apeși Adaugă la aromele dorite.', 'ru' => 'Выбери говядину или курицу и нажми «Добавить» у нужных вкусов.'],
        'how_2' => ['en' => 'Send', 'ro' => 'Trimiți', 'ru' => 'Отправь'],
        'how_2_d' => ['en' => 'Open the cart, leave your name and phone. No account, no card.', 'ro' => 'Deschizi coșul, lași numele și telefonul. Fără cont, fără card.', 'ru' => 'Открой корзину, оставь имя и телефон. Без регистрации и карты.'],
        'how_3' => ['en' => 'Eat', 'ro' => 'Mănânci', 'ru' => 'Ешь'],
        'how_3_d' => ['en' => 'Ed calls to set the day, brings it to you, and you pay at the door.', 'ro' => 'Ed te sună pentru zi, ți-l aduce, iar plătești la ușă.', 'ru' => 'Эд позвонит, договорится о дне, привезёт, и ты заплатишь при получении.'],

        // ---- Shop (the lineup) ----
        'shop_kicker' => ['en' => 'Order here', 'ro' => 'Comandă aici', 'ru' => 'Заказ здесь'],
        'shop_title' => ['en' => 'Pick your jerky.', 'ro' => 'Alege-ți jerky-ul.', 'ru' => 'Выбери своё джерки.'],
        'shop_lead' => ['en' => 'Choose the meat, then tap Add. Every pack is {g} g. You pay at the door.', 'ro' => 'Alege carnea, apoi apasă Adaugă. Fiecare pachet are {g} g. Plătești la ușă.', 'ru' => 'Выбери мясо и нажми «Добавить». В каждой упаковке {g} г. Оплата при получении.'],
        'meat_label' => ['en' => 'Meat', 'ro' => 'Carne', 'ru' => 'Мясо'],
        'mood_label' => ['en' => 'In the mood for', 'ro' => 'Ai poftă de', 'ru' => 'Хочется'],
        'mood_sweet' => ['en' => 'Sweet', 'ro' => 'Dulce', 'ru' => 'Сладкого'],
        'mood_smoky' => ['en' => 'Smoky', 'ro' => 'Afumat', 'ru' => 'Дымного'],
        'mood_savoury' => ['en' => 'Savoury', 'ro' => 'Savuros', 'ru' => 'Пряного'],
        'mood_hot' => ['en' => 'Hot', 'ro' => 'Iute', 'ru' => 'Острого'],
        'mood_any' => ['en' => 'Can’t decide', 'ro' => 'Nu mă pot decide', 'ru' => 'Не знаю'],
        'taste_sweet' => ['en' => 'Sweet', 'ro' => 'Dulce', 'ru' => 'Сладость'],
        'taste_smoky' => ['en' => 'Smoky', 'ro' => 'Afumat', 'ru' => 'Дымок'],
        'taste_garlic' => ['en' => 'Garlic', 'ro' => 'Usturoi', 'ru' => 'Чеснок'],
        'taste_heat' => ['en' => 'Heat', 'ro' => 'Iuțeală', 'ru' => 'Острота'],
        'add' => ['en' => 'Add', 'ro' => 'Adaugă', 'ru' => 'Добавить'],
        'add_cart' => ['en' => 'Add to cart', 'ro' => 'Adaugă în coș', 'ru' => 'В корзину'],
        'in_cart' => ['en' => 'in cart', 'ro' => 'în coș', 'ru' => 'в корзине'],
        'go_checkout' => ['en' => 'Go to checkout', 'ro' => 'Mergi la comandă', 'ru' => 'Перейти к оформлению'],
        'taster_meta' => ['en' => '6 packs × 50 g · beef & chicken', 'ro' => '6 pachete × 50 g · vită și pui', 'ru' => '6 упаковок × 50 г · говядина и курица'],
        'taster_lead' => ['en' => 'Can’t decide? A 50 g pack of every flavour, beef and chicken.', 'ro' => 'Nu te poți decide? Câte un pachet de 50 g din fiecare aromă, vită și pui.', 'ru' => 'Не можете выбрать? По упаковке 50 г каждого вкуса, говядина и курица.'],

        // ---- Up close (flavour chapters) ----
        'flav_kicker' => ['en' => 'Up close', 'ro' => 'De aproape', 'ru' => 'Крупным планом'],
        'flav_title' => ['en' => 'Every flavour, up close.', 'ro' => 'Fiecare aromă, de aproape.', 'ru' => 'Каждый вкус крупным планом.'],
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
        'in_order' => ['en' => 'in cart', 'ro' => 'în coș', 'ru' => 'в корзине'],
        'fewer' => ['en' => 'One fewer {name}', 'ro' => 'Cu unul mai puțin: {name}', 'ru' => 'На одну меньше: {name}'],
        'more' => ['en' => 'One more {name}', 'ro' => 'Încă unul: {name}', 'ru' => 'Ещё одну: {name}'],
        'pack_alt' => ['en' => 'The {name} pack', 'ro' => 'Pachetul {name}', 'ru' => 'Упаковка {name}'],

        // ---- Click to order bands ----
        'cta_1_big' => ['en' => 'Click to order', 'ro' => 'Comandă acum', 'ru' => 'Заказать'],
        'cta_1_small' => ['en' => 'Pick your packs. Pay at the door.', 'ro' => 'Alege pachetele. Plătești la ușă.', 'ru' => 'Выбери упаковки. Оплата при получении.'],
        'cta_2_big' => ['en' => 'Hungry yet?', 'ro' => 'Ți-e foame?', 'ru' => 'Проголодался?'],
        'cta_2_small' => ['en' => 'Click to order. It takes a minute.', 'ro' => 'Comandă acum. Durează un minut.', 'ru' => 'Закажи сейчас. Это займёт минуту.'],

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

        // ---- Who's Ed ----
        'ed_kicker' => ['en' => 'Who’s Ed', 'ro' => 'Cine e Ed', 'ru' => 'Кто такой Эд'],
        'ed_title' => ['en' => 'One man. One kitchen. No shortcuts.', 'ro' => 'Un om. O bucătărie. Fără scurtături.', 'ru' => 'Один человек. Одна кухня. Без компромиссов.'],
        'ed_text' => ['en' => 'Ed doesn’t run a factory. He runs a kitchen, after dark, with premium beef and chicken, a sharp knife and a lot of patience. Every strip is sliced by hand, every flavour gets its own marinade, and everything dries slowly until it’s right. Then he packs it, calls you, and drives it to your door himself.', 'ro' => 'Ed nu are o fabrică. Are o bucătărie, noaptea târziu, carne premium de vită și de pui, un cuțit ascuțit și multă răbdare. Fiecare fâșie e tăiată manual, fiecare aromă are marinada ei și totul se usucă încet, până e exact cum trebuie. Apoi îl ambalează, te sună și ți-l aduce chiar el la ușă.', 'ru' => 'У Эда нет фабрики. У него есть кухня, поздняя ночь, отборная говядина и курица, острый нож и много терпения. Каждая полоска нарезается вручную, у каждого вкуса свой маринад, и всё медленно сушится, пока не станет как надо. Потом он упаковывает, звонит вам и сам привозит заказ к двери.'],
        'facts_title' => ['en' => 'Ed Facts', 'ro' => 'Fișa lui Ed', 'ru' => 'Факты об Эде'],
        'facts_serving' => ['en' => 'Serving size: one man, one kitchen', 'ro' => 'Porție: un om, o bucătărie', 'ru' => 'Порция: один человек, одна кухня'],
        'facts_1_k' => ['en' => 'Slicing', 'ro' => 'Tăiere', 'ru' => 'Нарезка'],
        'facts_1_v' => ['en' => 'By hand', 'ro' => 'Manual', 'ru' => 'Вручную'],
        'facts_2_k' => ['en' => 'Marinade', 'ro' => 'Marinadă', 'ru' => 'Маринад'],
        'facts_2_v' => ['en' => 'One per flavour', 'ro' => 'Una pentru fiecare aromă', 'ru' => 'Свой для каждого вкуса'],
        'facts_3_k' => ['en' => 'Drying', 'ro' => 'Uscare', 'ru' => 'Сушка'],
        'facts_3_v' => ['en' => 'Slow', 'ro' => 'Lentă', 'ru' => 'Медленная'],
        'facts_4_k' => ['en' => 'Batch size', 'ro' => 'Mărimea lotului', 'ru' => 'Размер партии'],
        'facts_4_v' => ['en' => 'Small', 'ro' => 'Mică', 'ru' => 'Небольшой'],
        'facts_5_k' => ['en' => 'Factories', 'ro' => 'Fabrici', 'ru' => 'Фабрик'],
        'facts_5_v' => ['en' => '0', 'ro' => '0', 'ru' => '0'],
        'facts_6_k' => ['en' => 'Delivered by', 'ro' => 'Livrat de', 'ru' => 'Доставляет'],
        'facts_6_v' => ['en' => 'Ed himself', 'ro' => 'Ed personal', 'ru' => 'Сам Эд'],
        'facts_7_k' => ['en' => 'Payment', 'ro' => 'Plata', 'ru' => 'Оплата'],
        'facts_7_v' => ['en' => 'At the door', 'ro' => 'La ușă', 'ru' => 'При получении'],
        'facts_note' => ['en' => 'Contains soy. Contains no shortcuts.', 'ro' => 'Conține soia. Nu conține scurtături.', 'ru' => 'Содержит сою. Не содержит компромиссов.'],

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

        // ---- Cart drawer ----
        'sheet_title' => ['en' => 'Your cart', 'ro' => 'Coșul tău', 'ru' => 'Ваша корзина'],
        'sheet_close' => ['en' => 'Close', 'ro' => 'Închide', 'ru' => 'Закрыть'],
        'sheet_empty' => ['en' => 'Your cart is empty. Pick a flavour first.', 'ro' => 'Coșul e gol. Alege mai întâi o aromă.', 'ru' => 'Корзина пуста. Сначала выберите вкус.'],
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
