# checkbox
Плагін інтеграції WooCommerce з Checkbox.ua, сервісом програмної реєстрації розрахункових операцій (пРРО).

== Description ==

Плагін дозволяє створювати чеки з замовлень WooCommerce за допомогою сервісу Checkbox. Після створення чеку, Checkbox відправить його на вказаний в замовлені e-mail. Також ви можете відправляти url чека на номер телефону (смс, Viber, Telegram - для цього потрібні сторонні плагіни та сервіси)

Зміну можна відкривати як автоматично (wp_cron) так і вручну. Чеки також створюються автоматично, згідно правил, або вручну.

Для роботи плагіна необхідно мати зареєстрований акаунт на сервісі [Checkbox](https://my.checkbox.ua/auth/registration?partner_ref=KMdBNlXSMp).

Ознайомитися з умовами використання і політикою конфіденційності Checkbox можна ознайомитися за посиланнями:
[Terms Of Service](https://docs.google.com/document/d/1TcnFPdHqc6yiel8vRjIvT3YOLOLnhN9-LIj80tQUOhU)
[Privacy](https://checkbox.in.ua/policy)

== Інструкція ==

https://youtu.be/ZbbcS9fx4cQ

1. Встановіть і активуйте плагін.

2. На сторінці налаштувань плагіна обовз'язково вкажіть:
- логін і пароль касира
- прізвище та ім'я касира
- ліцензійний ключ каси ( приклад: 27e1d40eedc2dc59d5952a )
- код податку

3. Виберіть спосіб підпису. Доступні два механізми підпису чеків: <br> Checkbox Підпис — утиліта, що встановлюється на будь-якому комп’ютері з доступом до Інтернету, або Checkbox Cloud, — сертифікований хмарний сервіс який доступний в особистому кабінеті Checkbox. Відео-інструкція для завантаження ключа ЕЦП в хмарний сервіс Checkbox — https://fb.watch/5Ufq6UBPJ2/.

4. В правилах автоматичного створення чеків заповніть всі поля для всіх доступних способів оплати.

5. За бажанням можете активувати додаткові опції плагіна:
"Автоматичне відкриття зміни" — зміна автоматично відкривається щодня 00:01
"Автоматично створювати чеки за правилами"

6. Натисність "Зберегти".

6. Відкрийте зміну касира за допомогою віджета "Checkbox" в адмін-панелі. Зміна не відкриється, якщо не запущений агент підпису або не завантажено підпис у Checkbox Cloud. Детально: https://youtu.be/E0ko9tr9ujg.

7. На сторінці замовлення, зверху в правій боковій панелі у випадаючому меню, виберіть опцію "Створити чек" та натисніть ">"

8. В примітках до замовлення з'явиться повідомлення про створення чека або про помилку.

9. ID чека відображатиметься у відповідному стовпчику на сторінці WooCommerce -> Замовлення а також додається до Custom Fields замовлення.

== Frequently Asked Questions ==

= Чи підтримуються коди УКТЗЕД? =

Ця функція доступна у платній версії. Напишіть на support@morkva.co.ua

= Чи підійде плагін для двомовних сайтів? =

По замовчуванню, плагін бере назву товару з замовлення. Якщо замовлення зроблено в російській версії - в чек передаватиметься російська назва. У платній версії можна задати атрибут з якого буде формуватися назва товару українською. Напишіть на support@morkva.co.ua

= Як перевірити створення чеків? =

При створенні акаунта в Чекбоксі, вам уже підготують тестового касира та касу. З цими даними можете створити скільки завгодно тестових чеків.

= Зміна відкривається, але не створюється чек для деяких замовлень. Помилка validation error =

У вас або не введений вірно код податку в налаштуваннях плагіна, або в замовленні є товар за 0грн. Ставте безкоштовним товарам символічно 0.01грн або 1грн

== Підтримка ==

Якщо виникла помилка при встановленні або використанні плагіна - пишіть на support@morkva.co.ua
Робочі години з 10:00 до 19:00 ПН-ПТ. Ми відповімо вам протягом доби в робочий час. Всі звернення опрацьовуються по черзі.

== Screenshots ==

1. Сторінка налаштувань
2. Віджет відкриття/закриття зміни касира
3. Створення чека вручну на сторінці замовлення
4. ID чека та його URL записуються у Custom Fields
5. Код податку в налаштуваннях повинен співпадати з кодом податку зі сторінки Податкові ставки

== Що нового? ==

= 2.2.4 =
* [fixed] виправили помилку налаштувань

= 2.2.3 =
* [fixed] виправили вивід шорткодів у чеку

= 2.2.2 =
* [fixed] прибрали вивід запиту в замовленні 

= 2.2.1 =
* [new] незначні правки в плагіні
* WooCommerce 8.x tested
* WordPress 6.3 tested

= 2.2.0 =
* [new] додали поле label для більш точної назви підтипу оплати (наприклад для типу cashless можна задати більш точне визначення: Картка, Післяплата, тощо.)

= 2.1.3 =
* [new] додали перевірку ціни на нуль

= 2.1.2 =
* [fixed] прибрали виклик помилок api та post_id

= 2.1.1 =
* [fixed] додали перевірку поля правил

= 2.1.0 =
* [new] змінили алгоритм додавання знижки до чеку - тепер знижки додаютья індивідуально для кожної позиції в чеку
* [new] додали можливість зміни назви знижки в налаштуваннях

= 2.0.1 =
* [fixed] виправили помилку зі зміною

= 2.0.0 =
* УВАГА, якщо ви оновлюєте плагін з версії 1.х, перезбережіть налаштування.
* Повністю переробили функціонал та UX/UI плагіна, зробили його стабільним та простим в налаштуванні
* Додали окремий мета-бокс створення чека на сторінку редагування товару
* WooCommerce 7.6 tested
* WordPress 6.2 tested

= 1.2.0 =
* [fixed] видалили крон відкриття зміни
* [fixed] виправили можливість відключення автоматичного відкриття зміни

= 1.1.0 =
* [new] прибрали крон закриття зміни. Налаштовуйте автоматичне закриття зміни на стороні кабінету Чекбокс
* [dev] зміни в архітектурі плагіна
* [dev] перевірили сумісність з WordPress 6.1 WooCommerce 7.3

= 1.0.1 =
* [fixed] виправили алгоритм роботи з цінами товарів у замовленні

= 1.0.0 =
* [fixed] виправили алгоритм роботи зі знижками та купонами

= 0.9.0 =
* [fixed] виправили помилку з автоматичним відкриттям зміни на деяких сайтах
* [dev] перевірили сумістість з WooComerce 7.0

= 0.8.5 =
* перевірено сумісність з WordPress 6.0 
* перевірено сумісність з WooCommerce 6.7.0
* дрібні допрацювання плагіну

= 0.8.4 =
* видалено freemius

= 0.8.3 =
* перевірено сумісніть з WordPress 5.9
* змінили текстові пояснення в інтерфейсі
* прибрали не обов'язкове поле departament з запиту на створення чеку

= 0.8.2 =
* додано крон-завдання на відкриття зміни о 00:01 (Київ) за умови, якщо функція "автоматичне відкриття зміни" активна
* додано можливість друкування чеку по лінку в таблиці замовлення і в блоці повідомлень на сторінці редагування замовлення
* виправлено незначні баги

= 0.8.1 =
* до замовлення додано кастомне поле receipt_url, в яке буде записуватися лінк на чек після його створення
* вдосконалено обробку помилок і виключень
* виправлено незначні баги

= 0.8.0 =
* розширили правила формування чеків (тепер статус замовлення індивідуальний для кожного способу оплати)
* додали поле “Код податку”
* додали поле “Службова інформація”, значення якого відображатиметься в нижній частині чеку
* додали режим логування
! ПРИ ОНОВЛЕННІ ДО ЦІЄЇ ВЕРСІЇ ПЕРЕЗБЕРЕЖІТЬ НАЛАШТУВАННЯ ПЛАГІНА І ПРОПИШІТЬ КОД ПОДАТКУ ТА СТАТУСИ ЗАМОВЛЕНЬ ВІДПОВІДНО ДО СПОСОБІВ ОПЛАТИ

= 0.7.1 =
* повідомлення з помилкою у разі не створення чеку зроблено більш інформативним
* виправлено незначні баги

= 0.7.0 =
* додано можливість прикріпляти податковий номер до кожного товару з замовлення
* виправлено незначні баги

= 0.6.4 =
* виправлено баг пов'язаний отримання увімкнених платіжних шлюзів

= 0.6.3 =
* виправлено незначні баги
* вдосконалено механізм моніторингу стабільності плагіна

= 0.6.2 =
* додали механізм моніторингу стабільності плагіна
* перевірено сумісність з WooCommerce 5.9.0

= 0.6.1 =
* виправлено незначні баги
* перевірено сумісність з WooCommerce 5.8.0

= 0.6.0 =
* додана можливіть вибору статусів замовлення при зміні на які автоматично створюватиметься чек

= 0.5.2 =
* додано підказки до опцій на сторінці налаштувань
* додано можливіть ввімкнення тестового режиму
* виправлено незначні баги

= 0.5.1 =
* перевірено сумісність з WordPress 5.8.1 і WooCommerce 5.7.1
* виправлено незначні баги

= 0.5.0 =
* додано опцію "Спосіб підпису"
* додана можливість пропускати створення чека при активній опції "Автоматично створювати чеки при статусі Виконано"
* перевірено сумісність з WordPress 5.8 і WooCommerce 5.5.1
* виправлено незначні баги

= 0.4.1 =
* виправлено критичну помилку
* виправлено незначні баги

= 0.4.0 =
* додано функцію автоматичного відкриття зміни
* перевірено сумісність з WordPress 5.7.2 і WooCommerce 5.3.0
* виправлена функція автоматичного створення чека при статусі Виконано
* виправлено незначні баги

= 0.3.1 =
* виправлено помилку з e-mail покупця
* ім’я і прізвище касира можна задавати в налаштуваннях плагіна

= 0.2.3 =
* виправлено передаванням кількості товарів

= 0.2.2 =
* перевірено сумісність з WordPress 5.6 і WooCommerce 4.8
* виправлено помилку з кількістю

= 0.2.1 =
* виправлено незначні баги

= 0.1.0 =
* незначні правки коду

= 0.0.1 =
* beta-версія плагіна
