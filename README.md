# checkbox
Плагін інтеграції WooCommerce з Checkbox, сервісом програмної реєстрації розрахункових операцій.

== Description ==

Плагін дозволяє фіскалізувати виконані замовлення в WooCommerce за допомогою сервісу Checkbox.

Для роботи плагіна необхідно мати зареєстрований акаунт на сервісі Checkbox (https://checkbox.in.ua)

Ознайомитися з умовами використання і політикою конфіденційності Checkbox можна ознайомитися за посиланнями:
Terms Of Service — https://docs.google.com/document/d/1TcnFPdHqc6yiel8vRjIvT3YOLOLnhN9-LIj80tQUOhU
Privacy — https://checkbox.in.ua/policy

== Інструкція ==

1. Встановіть і активуйте плагін.

2. На сторінці налаштувань плагіна обовз'язково вкажіть:
- логін і пароль касира
- прізвище та ім'я касира
- ліцензійний ключ віртуального касового апарату ( приклад: 27e1d40eedc2dc59d5952a )

* За бажанням можете активувати додаткові опції плагіна: <br> "Автоматичне відкриття зміни" — зміна автоматично відкривається при першому створенні чека <br> "Автоматично створювати чеки при статусі Виконано" — автоматично створюється чек до замовлення при зміні статусу на Виконано.

3. Виберіть спосіб підпису. Доступні два механізми підпису чеків: <br> Checkbox Підпис — утиліта, що встановлюється на будь-якому комп’ютері з доступом до Інтернету, і HSM, або Checkbox Cloud, — сертифікований хмарний сервіс для генерації та зберігання ключів DepositSign, у разі вибору якого необхідність встановлення будь-якого ПЗ для роботи з ЕЦП відсутня. Відео-інструкція для завантаження ключа ЕЦП в хмарний сервіс Checkbox — https://fb.watch/5Ufq6UBPJ2/.

4. В налаштуваннях платіжної системи визначте тип (cash або cashless) для кожного способу оплати, який наявний в таблиці. Якщо активована функція автоматичного створення чека при статусі Виконано, то визначте для кожного способу оплати пропускати створення чека чи ні.

5. Натисність "Зберегти".

6. Відкрийте зміну касира за допомогою віджета "Checkbox" в адмін-панелі. Зміна не відкриється, якщо не запущений агент підпису. Детально: https://youtu.be/E0ko9tr9ujg.

7. На сторінці замовлення, зверху в правій боковій панелі у випадаючому меню, виберіть опцію "Створити чек" та натисніть ">"

8. В примітках до замовлення з'явиться повідомлення про створення чека або про помилку.

9. ID чека відображатиметься у відповідному стовпчику на сторінці WooCommerce -> Замовлення

== Що нового? ==

= 0.5.0 =
* додано опцію "Спосіб підпису"
* додана можливість пропускати створення чека при активній опції "Автоматично створювати чеки при статусі Виконано"
* налаштована сумісність з WordPress 5.8 і WooCommerce 5.5.1
* виправлено незначні баги

= 0.4.1 =
* виправлено критичну помилку
* виправлено незначні баги

= 0.4.0 =
* додано функцію автоматичного відкриття зміни
* налаштована сумісність з WordPress 5.7.2 і WooCommerce 5.3.0
* виправлена функція автоматичного створення чека при статусі Виконано
* виправлено незначні баги

= 0.3.1 =
* виправлено помилку з e-mail покупця
* ім’я і прізвище касира можна задавати в налаштуваннях плагіна

= 0.2.3 =
* виправлено передаванням кількості товарів

= 0.2.2 =
* налаштована сумісність з WordPress 5.6 і WooCommerce 4.8
* виправлено помилку з кількістю

= 0.2.1 =
* виправлено незначні баги

= 0.1.0 =
* незначні правки коду

= 0.0.1 =
* beta-версія плагіна


