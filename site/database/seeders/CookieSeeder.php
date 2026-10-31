<?php

namespace Database\Seeders;

use App\Models\Policy;
use App\Models\PolicyTrans;
use Illuminate\Database\Seeder;

class CookieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $policiesData = [

            // 1
            [
                'title_ro' => 'Introducere',
                'text_ro' => 'Această politică explică modul în care folosim module cookie pe site-ul nostru web pentru a îmbunătăți experiența utilizatorului și pentru a oferi conținut și reclame personalizate.',
                'title_ru' => 'Введение',
                'text_ru' => 'Эта политика объясняет, как мы используем файлы cookie на нашем веб-сайте для улучшения взаимодействия с пользователем и предоставления персонализированного контента и рекламы.',
                'title_en' => 'Introduction',
                'text_en' => 'This policy explains how we use cookies on our website to enhance user experience and provide personalized content and ads.',
            ],
            // 2
            [
                'title_ro' => 'Ce sunt cookie-urile?',
                'text_ro' => 'Cookie-urile sunt fișiere text mici care sunt stocate pe dispozitivul dvs. atunci când vizitați site-ul nostru web. Acestea ne permit să vă amintim preferințele, să personalizăm conținutul, să analizăm traficul și să ne îmbunătățim serviciile.',
                'title_ru' => 'Что такое файлы cookie?',
                'text_ru' => 'Файлы cookie — это небольшие текстовые файлы, которые сохраняются на вашем устройстве, когда вы посещаете наш веб-сайт. Они позволяют нам запоминать ваши предпочтения, персонализировать контент, анализировать трафик и улучшать наши услуги.',
                'title_en' => 'What are Cookies?',
                'text_en' => 'Cookies are small text files that are stored on your device when you visit our website.
                They allow us to remember your preferences, personalize content, analyze traffic, and improve our services.',
            ],



            // 3
            [
                'title_ro' => 'Tipuri de cookie-uri',
                'text_ro' => 'Folosim cookie-uri de sesiune și cookie-uri persistente.
                Cookie-urile de sesiune sunt temporare și sunt șterse atunci când închideți browserul.
                Cookie-urile persistente rămân pe dispozitivul dumneavoastră pentru o anumită perioadă de timp sau până când le ștergeți.',
                'title_ru' => 'Типы файлов cookie',
                'text_ru' => 'Мы используем файлы cookie сеанса и постоянные файлы cookie.
                Сеансовые файлы cookie являются временными и удаляются при закрытии браузера.
                Постоянные файлы cookie остаются на вашем устройстве в течение установленного периода времени или до тех пор, пока вы их не удалите.',
                'title_en' => 'Types of Cookies',
                'text_en' => 'We use session cookies and persistent cookies.
                Session cookies are temporary and are deleted when you close your browser.
                Persistent cookies remain on your device for a set period of time or until you delete them.',
            ],




            // 4
            [
                'title_ro' => 'Scopul cookie-urilor',
                'text_ro' => 'Folosim cookie-uri pentru a vă aminti preferințele, cum ar fi setările de limbă și locație.
                Folosim cookie-uri pentru a personaliza conținutul și reclamele în funcție de istoricul de navigare și comportamentul dumneavoastră pe site-ul nostru.
                Folosim cookie-uri pentru a analiza traficul site-ului web și comportamentul utilizatorilor pentru a ne îmbunătăți serviciile și conținutul.',
                'title_ru' => 'Цель файлов cookie',
                'text_ru' => 'Мы используем файлы cookie, чтобы запомнить ваши предпочтения, такие как настройки языка и местоположения.
                Мы используем файлы cookie для персонализации контента и рекламы на основе вашей истории просмотров и поведения на нашем веб-сайте.
                Мы используем файлы cookie для анализа трафика веб-сайта и поведения пользователей, чтобы улучшить наши услуги и контент.',
                'title_en' => 'Purpose of Cookies',
                'text_en' => 'We use cookies to remember your preferences, such as language and location settings.
                We use cookies to personalize content and ads based on your browsing history and behavior on our website.
                We use cookies to analyze website traffic and user behavior to improve our services and content.',
            ],

            // 5
            [
                'title_ro' => 'Cookie-uri de la terți',
                'text_ro' => 'De asemenea, putem folosi cookie-uri de la terți, cum ar fi cele de la Google Analytics sau de la partenerii de publicitate, în scopuri de analiză și publicitate.
                Cookie-urile de la terți sunt supuse politicilor și practicilor de confidențialitate ale acelor furnizori terți.',
                'title_ru' => 'Сторонние файлы cookie',
                'text_ru' => 'Мы также можем использовать сторонние файлы cookie, например файлы Google Analytics или рекламных партнеров, для аналитических и рекламных целей.
                Сторонние файлы cookie регулируются политиками и правилами конфиденциальности этих сторонних поставщиков.',
                'title_en' => 'Third-Party Cookies',
                'text_en' => 'We may also use third-party cookies, such as those from Google Analytics or advertising partners, for analytics and advertising purposes.
                Third-party cookies are subject to the policies and privacy practices of those third-party providers.',
            ],

            // 6
            [
                'title_ro' => 'Gestionarea cookie-urilor',
                'text_ro' => 'Puteți gestiona și șterge cookie-urile prin setările browserului dvs.
                Dezactivarea cookie-urilor vă poate limita capacitatea de a utiliza anumite funcții ale site-ului nostru.',
                'title_ru' => 'Управление файлами cookie',
                'text_ru' => 'Вы можете управлять файлами cookie и удалять их в настройках своего браузера.
                Отключение файлов cookie может ограничить вашу способность использовать определенные функции нашего веб-сайта.',
                'title_en' => 'Managing Cookies',
                'text_en' => 'You can manage and delete cookies through your browser settings.
                Disabling cookies may limit your ability to use certain features of our website.',
            ],

            // 7
            [
                'title_ro' => 'Consimțământul pentru cookie-uri',
                'text_ro' => 'Continuând să utilizați site-ul nostru, sunteți de acord cu utilizarea cookie-urilor în conformitate cu această politică.',
                'title_ru' => 'Согласие с файлами cookie',
                'text_ru' => 'Продолжая использовать наш веб-сайт, вы соглашаетесь на использование нами файлов cookie в соответствии с этой политикой.',
                'title_en' => 'Consent to Cookies',
                'text_en' => 'By continuing to use our website, you consent to our use of cookies in accordance with this policy.',
            ],

            // 8
            [
                'title_ro' => 'Actualizări ale politicii',
                'text_ro' => 'Este posibil să actualizăm această politică privind cookie-urile din când în când. Reveniți regulat pentru actualizări.',
                'title_ru' => 'Обновления политики',
                'text_ru' => 'Мы можем время от времени обновлять эту политику в отношении файлов cookie. Регулярно проверяйте наличие обновлений.',
                'title_en' => 'Updates to Policy',
                'text_en' => 'We may update this cookie policy from time to time. Check back regularly for updates.',
            ],

            // 9
            [
                'title_ro' => 'Cookie-uri esențiale',
                'text_ro' => 'Folosim cookie-uri esențiale care sunt necesare pentru ca site-ul să funcționeze corect.
                Cookie-urile esențiale nu colectează informații personale.',
                'title_ru' => 'Основные файлы cookie',
                'text_ru' => 'Мы используем основные файлы cookie, необходимые для правильной работы веб-сайта.
                Основные файлы cookie не собирают никакой личной информации.',
                'title_en' => 'Essential Cookies',
                'text_en' => 'We use essential cookies that are necessary for the website to function properly.
                Essential cookies do not collect any personal information.',
            ],

            // 10
            [
                'title_ro' => 'Cookie-uri funcționale',
                'text_ro' => 'Folosim cookie-uri funcționale care sporesc gradul de utilizare și funcționalitatea site-ului web.
                Cookie-urile funcționale pot colecta informații personale, cum ar fi numele sau adresa de e-mail.',
                'title_ru' => 'Функциональные файлы cookie',
                'text_ru' => 'Мы используем функциональные файлы cookie, которые повышают удобство использования и функциональность веб-сайта.
                Функциональные файлы cookie могут собирать личную информацию, такую как ваше имя или адрес электронной почты.',
                'title_en' => 'Functional Cookies',
                'text_en' => 'We use functional cookies that enhance the usability and functionality of the website.
                Functional cookies may collect personal information such as your name or email address.',
            ],

            // 11
            [
                'title_ro' => 'Cookie-uri de performanță',
                'text_ro' => 'Folosim module cookie de performanță pentru a monitoriza și îmbunătăți performanța site-ului web.
                Cookie-urile de performanță nu colectează informații personale.',
                'title_ru' => 'Файлы cookie производительности',
                'text_ru' => 'Мы используем файлы cookie производительности, чтобы отслеживать и улучшать работу веб-сайта.
                Файлы cookie производительности не собирают никакой личной информации.',
                'title_en' => 'Performance Cookies',
                'text_en' => 'English text 2',
            ],

            // 12
            [
                'title_ro' => 'Cookie-uri de direcționare și publicitate',
                'text_ro' => 'Folosim cookie-uri de direcționare și de publicitate pentru a furniza conținut și reclame personalizate.
                Cookie-urile de direcționare și de publicitate pot colecta informații personale, cum ar fi locația dvs. sau istoricul de navigare.',
                'title_ru' => 'Таргетинговые и рекламные файлы cookie',
                'text_ru' => 'Мы используем целевые и рекламные файлы cookie для предоставления персонализированного контента и рекламы.
                Целевые и рекламные файлы cookie могут собирать личную информацию, такую как ваше местоположение или историю просмотров.',
                'title_en' => 'Targeting and Advertising Cookies',
                'text_en' => 'We use targeting and advertising cookies to deliver personalized content and ads.
                Targeting and advertising cookies may collect personal information such as your location or browsing history.',
            ],

            // 13
            [
                'title_ro' => 'Cookie-uri pentru rețelele de socializare',
                'text_ro' => 'Este posibil să folosim module cookie pentru rețelele sociale pentru a permite partajarea și interacțiunea cu site-ul nostru pe rețelele sociale.
                Cookie-urile de rețele sociale sunt supuse politicilor și practicilor de confidențialitate ale platformelor de rețele sociale.',
                'title_ru' => 'Файлы cookie социальных сетей',
                'text_ru' => 'Мы можем использовать куки-файлы социальных сетей, чтобы обеспечить совместное использование социальных сетей и взаимодействие с нашим веб-сайтом.
                Файлы cookie социальных сетей регулируются политиками и правилами конфиденциальности платформ социальных сетей.',
                'title_en' => 'Social Media Cookies',
                'text_en' => 'We may use social media cookies to enable social media sharing and interaction with our website.
                Social media cookies are subject to the policies and privacy practices of the social media platforms.',
            ],

            // 14
            [
                'title_ro' => 'Cookie-uri de analiză',
                'text_ro' => 'Folosim module cookie de analiză pentru a urmări utilizarea site-ului web și pentru a măsura eficiența conținutului și serviciilor noastre.
                Cookie-urile de analiză pot colecta informații personale, cum ar fi adresa dvs. IP sau tipul de browser.',
                'title_ru' => 'Аналитические файлы cookie',
                'text_ru' => 'Мы используем аналитические файлы cookie для отслеживания использования веб-сайта и измерения эффективности нашего контента и услуг.
                Файлы cookie Analytics могут собирать личную информацию, такую как ваш IP-адрес или тип браузера.',
                'title_en' => 'Analytics Cookies',
                'text_en' => 'We use analytics cookies to track website usage and measure the effectiveness of our content and services.
                Analytics cookies may collect personal information such as your IP address or browser type.',
            ],

            // 15
            [
                'title_ro' => 'Cookie-uri de securitate',
                'text_ro' => 'Folosim cookie-uri de securitate pentru a preveni frauda și pentru a ne proteja site-ul web și utilizatorii.
                Cookie-urile de securitate nu colectează informații personale.',
                'title_ru' => 'Файлы cookie безопасности',
                'text_ru' => 'Мы используем файлы cookie безопасности для предотвращения мошенничества и защиты нашего веб-сайта и пользователей.
                Файлы cookie безопасности не собирают никакой личной информации.',
                'title_en' => 'Security Cookies',
                'text_en' => 'We use security cookies to prevent fraud and protect our website and users.
                Security cookies do not collect any personal information.',
            ],

            // 16
            [
                'title_ro' => 'Cookie-uri de marketing',
                'text_ro' => 'Folosim cookie-uri de marketing pentru a ne promova produsele și serviciile prin campanii de publicitate direcționate.
                Cookie-urile de marketing pot colecta informații personale, cum ar fi numele sau adresa de e-mail.',
                'title_ru' => 'Маркетинговые файлы cookie',
                'text_ru' => 'Мы используем маркетинговые файлы cookie для продвижения наших продуктов и услуг с помощью целевых рекламных кампаний.
                Маркетинговые файлы cookie могут собирать личную информацию, такую как ваше имя или адрес электронной почты.',
                'title_en' => 'Marketing Cookies',
                'text_en' => 'We use marketing cookies to promote our products and services through targeted advertising campaigns.
                Marketing cookies may collect personal information such as your name or email address.',
            ],

            // 17
            [
                'title_ro' => 'Cookie-uri de funcționalitate',
                'text_ro' => 'Folosim cookie-uri de funcționalitate pentru a vă aminti preferințele și pentru a oferi funcții îmbunătățite.
                Cookie-urile de funcționalitate pot colecta informații personale, cum ar fi locația dvs. sau setările de limbă.',
                'title_ru' => 'Функциональные файлы cookie',
                'text_ru' => 'Мы используем функциональные файлы cookie, чтобы запоминать ваши предпочтения и предоставлять расширенные функции.
                Функциональные файлы cookie могут собирать личную информацию, такую как ваше местоположение или языковые настройки.',
                'title_en' => 'Functionality Cookies',
                'text_en' => 'English text 2',
            ],

            // 18
            [
                'title_ro' => 'Reclame',
                'text_ro' => 'Este posibil să folosim cookie-uri pentru a afișa reclame pe site-ul nostru web.
                Reclamele pot fi livrate de rețele de publicitate terțe.',
                'title_ru' => 'Объявления',
                'text_ru' => 'Мы можем использовать файлы cookie для отображения рекламы на нашем веб-сайте.
                Рекламные объявления могут доставляться сторонними рекламными сетями.',
                'title_en' => 'Advertisements',
                'text_en' => 'We may use cookies to display advertisements on our website.
                Advertisements may be delivered by third-party advertising networks.',
            ],

            // 19
            [
                'title_ro' => 'Renunțarea la cookie-uri',
                'text_ro' => 'Puteți renunța la publicitatea direcționată și cookie-urile de urmărire prin setările browserului sau prin instrumente de renunțare ale terților.',
                'title_ru' => 'Отказ от файлов cookie',
                'text_ru' => 'Вы можете отказаться от целевых рекламных и отслеживающих файлов cookie в настройках своего браузера или с помощью сторонних инструментов отказа.',
                'title_en' => 'Opting Out of Cookies',
                'text_en' => 'You can opt out of targeted advertising and tracking cookies through your browser settings or through third-party opt-out tools.',
            ],

            // 20
            [
                'title_ro' => 'Reținerea cookie-urilor',
                'text_ro' => 'Păstrăm cookie-uri pentru o perioadă de maximum doi ani.
                Unele cookie-uri pot fi păstrate pentru o perioadă mai lungă dacă este necesar din motive legale sau de securitate.',
                'title_ru' => 'Сохранение файлов cookie',
                'text_ru' => 'Мы храним файлы cookie в течение максимум двух лет.
                Некоторые файлы cookie могут храниться в течение более длительного периода, если это необходимо по юридическим причинам или по соображениям безопасности.',
                'title_en' => 'Cookie Retention',
                'text_en' => 'We retain cookies for a maximum period of two years.
                Some cookies may be retained for a longer period if necessary for legal or security reasons.',
            ],

            // 21
            [
                'title_ro' => 'Protejarea datelor',
                'text_ro' => 'Prelucrăm informațiile personale colectate prin cookie-uri în conformitate cu politica noastră de confidențialitate și cu legile aplicabile privind protecția datelor.
                Luăm măsuri tehnice și organizatorice adecvate pentru a proteja datele personale colectate prin cookie-uri.',
                'title_ru' => 'Защита данных',
                'text_ru' => 'Мы обрабатываем личную информацию, собранную с помощью файлов cookie, в соответствии с нашей политикой конфиденциальности и применимыми законами о защите данных.
                Мы принимаем соответствующие технические и организационные меры для защиты персональных данных, собранных с помощью файлов cookie.',
                'title_en' => 'Data Protection',
                'text_en' => 'We process personal information collected through cookies in accordance with our privacy policy and applicable data protection laws.
                We take appropriate technical and organisational measures to protect personal data collected through cookies.',
            ],

            // 22
            [
                'title_ro' => 'Revizuirea politicii cookie',
                'text_ro' => 'Revizuim și actualizăm în mod regulat politica noastră privind cookie-urile pentru a ne asigura că respectă legile aplicabile și reflectă practicile noastre actuale de prelucrare a datelor.',
                'title_ru' => 'Обзор политики использования файлов cookie',
                'text_ru' => 'Мы регулярно пересматриваем и обновляем нашу политику в отношении файлов cookie, чтобы убедиться, что она соответствует применимым законам и отражает наши текущие методы обработки данных.',
                'title_en' => 'Cookie Policy Review',
                'text_en' => 'We regularly review and update our cookie policy to ensure it complies with applicable laws and reflects our current data processing practices.',
            ],

            // 23
            [
                'title_ro' => 'Gestionarea consimțământului cookie-urilor',
                'text_ro' => 'Oferim mecanisme pentru gestionarea consimțământului pentru cookie-uri, cum ar fi bannere pentru cookie-uri sau centre de preferințe.',
                'title_ru' => 'Управление согласием на использование файлов cookie',
                'text_ru' => 'Мы предоставляем механизмы для управления согласием на использование файлов cookie, такие как баннеры файлов cookie или центры предпочтений.',
                'title_en' => 'Cookie Consent Management',
                'text_en' => 'We provide mechanisms for managing cookie consent, such as cookie banners or preference centers.',
            ],

            // 24
            [
                'title_ro' => 'Banner pentru cookie-uri',
                'text_ro' => 'Folosim un banner pentru cookie-uri pentru a informa utilizatorii despre utilizarea cookie-urilor și pentru a obține consimțământul lor, acolo unde este cerut de lege.',
                'title_ru' => 'Баннер с файлами cookie',
                'text_ru' => 'Мы используем баннер файлов cookie, чтобы информировать пользователей об использовании нами файлов cookie и получать их согласие, когда это требуется по закону.',
                'title_en' => 'Cookie Banner',
                'text_en' => 'We use a cookie banner to inform users about our use of cookies and obtain their consent where required by law.',
            ],

            // 25
            [
                'title_ro' => 'Retragerea consimțământului',
                'text_ro' => 'Vă puteți retrage oricând consimțământul pentru utilizarea cookie-urilor prin setările browserului sau centrul de preferințe pentru cookie-uri.',
                'title_ru' => 'Отзыв согласия',
                'text_ru' => 'Вы можете отозвать свое согласие на использование нами файлов cookie в любое время в настройках вашего браузера или в центре настроек файлов cookie.',
                'title_en' => 'Consent Withdrawal',
                'text_en' => 'You can withdraw your consent to our use of cookies at any time through your browser settings or cookie preference center.',
            ],

            // 26
            [
                'title_ro' => 'Preferințe cookie',
                'text_ro' => 'Oferim utilizatorilor opțiuni pentru a-și personaliza preferințele cookie, cum ar fi renunțarea la anumite categorii de cookie-uri.',
                'title_ru' => 'Настройки файлов cookie',
                'text_ru' => 'Мы предоставляем пользователям возможность настроить свои предпочтения в отношении файлов cookie, например, отказаться от определенных категорий файлов cookie.',
                'title_en' => 'Cookie Preferences',
                'text_en' => 'We provide users with options to customize their cookie preferences, such as opting out of specific categories of cookies.',
            ],

            // 27
            [
                'title_ro' => 'Descrieri categorii cookie',
                'text_ro' => 'Oferim descrieri clare și concise ale diferitelor categorii de cookie-uri pe care le folosim, inclusiv scopul lor și datele colectate.',
                'title_ru' => 'Описание категорий файлов cookie',
                'text_ru' => 'Мы предоставляем четкие и краткие описания различных категорий файлов cookie, которые мы используем, включая их назначение и собираемые данные.',
                'title_en' => 'Cookie Category Descriptions',
                'text_en' => 'We provide clear and concise descriptions of the different categories of cookies we use, including their purpose and data collected.',
            ],

            // 28
            [
                'title_ro' => 'Politica Cookie Accesibilitate',
                'text_ro' => 'Ne asigurăm că politica noastră privind cookie-urile este ușor accesibilă tuturor utilizatorilor, inclusiv celor cu dizabilități, prin formate alternative sau tehnologii de asistență.',
                'title_ru' => 'Политика использования файлов cookie',
                'text_ru' => 'Мы гарантируем, что наша политика в отношении файлов cookie легко доступна для всех пользователей, в том числе для людей с ограниченными возможностями, с помощью альтернативных форматов или вспомогательных технологий.',
                'title_en' => 'Cookie Policy Accessibility',
                'text_en' => 'We ensure our cookie policy is easily accessible to all users, including those with disabilities, through alternative formats or assistive technologies.',
            ],


            // 29
            [
                'title_ro' => 'Drepturile utilizatorului',
                'text_ro' => 'Aveți dreptul de a accesa, rectifica, șterge, restricționa și opune prelucrării datelor dumneavoastră cu caracter personal colectate prin cookie-uri, precum și dreptul la portabilitatea datelor, acolo unde este cazul.',
                'title_ru' => 'Права пользователя',
                'text_ru' => 'Вы имеете право на доступ, исправление, удаление, ограничение и возражение против обработки ваших личных данных, собранных с помощью файлов cookie, а также право на переносимость данных, где это применимо.',
                'title_en' => 'User Rights',
                'text_en' => 'You have the right to access, rectify, erase, restrict, and object to the processing of your personal data collected through cookies, as well as the right to data portability, where applicable.',
            ],

            // 30
            [
                'title_ro' => 'Informații de contact',
                'text_ro' => 'Dacă aveți întrebări sau nelămuriri cu privire la utilizarea cookie-urilor, vă rugăm să ne contactați la [inserați informațiile de contact].',
                'title_ru' => 'Контактная информация',
                'text_ru' => 'Если у вас есть какие-либо вопросы или опасения по поводу использования нами файлов cookie, свяжитесь с нами по адресу [вставьте контактную информацию].',
                'title_en' => 'Contact Information',
                'text_en' => 'If you have any questions or concerns about our use of cookies, please contact us at [insert contact information].',
            ],


        ];

        // create 30 policy entries
        foreach ($policiesData as $policyData) {
            $policy = Policy::create([
                'active' => true // set all policies to active
            ]);

            // create translations per policy
            foreach (['ro', 'ru', 'en'] as $lang) {
                PolicyTrans::create([
                    'title' => $policyData["title_$lang"],
                    'text' => $policyData["text_$lang"],
                    'lang' => $lang,
                    'policy_id' => $policy->id
                ]);
            }
        }
    }


}
