# checkbox
Плагін інтеграції з РРО Checkbox: ручне або автоматичне створення електронних чеків з замовлень і відправка їх у ваш особистий кабінет на Checkbox та на e-mail покупця.

== Description ==

Плагін дозволяє автоматично або вручну створювати електронні чеки, які можна потім використовувати в інших CRM або плагінах.

Для роботи плагіна необхідно мати акаунт на платформі Checkbox (https://checkbox.in.ua)

Ознайомитися з умовами використання і політикою конфіденційності Checkbox можна ознайомитися за посиланнями:
Terms Of Service — https://docs.google.com/document/d/1TcnFPdHqc6yiel8vRjIvT3YOLOLnhN9-LIj80tQUOhU
Privacy — https://checkbox.in.ua/policy

== Інструкція ==

1. Встановіть і активуйте плагін.

2. На сторінці налаштувань плагіна вкажіть:
- логін касира і пароль користувача ( не касира ) від особистого кабінету Checkbox
- прізвище та ім'я касира
- ключ каси ( приклад: 27e1d40eedc2dc59d5952a )
- натисність "Зберегти"

* За бажанням можете активувати додаткові опції плагіну:
"Автоматичне відкриття зміни" -- зміна автоматично відкривається при першому створенні чека
"Автоматично створювати чеки при статусі Виконано" -- автоматично створюється чек до замовлення при зміні статусу на Виконано

3. На головній сторінці адмін-панелі у віджеті плагіну "РРО Статус" відкрийте зміну касира. Зміна не відкриється, якщо не запущена програма електронного підпису. Детально: https://youtu.be/E0ko9tr9ujg

4. На сторінці замовлення, зверху в правій боковій панелі у випадаючому меню, виберіть опцію "Створити чек" та натисніть ">"

5. В примітках до замовлення з'явиться повідомлення про створення чеку або про помилку.

6. ID чека відображатиметься у відповідному стовпчику на сторінці WooCommerce -> Замовлення

== Що нового? ==

= 0.4.0 =
* додано функцію автоматичного відкриття зміни
* налаштована сумісність з WordPress 5.7.2 і WooCommerce 5.3.0
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


