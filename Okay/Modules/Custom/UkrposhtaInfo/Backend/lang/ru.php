<?php
$lang['up_update_all'] = 'Обновить все';
$lang['up_update'] = 'Обновить ';
$lang['up_update_regions'] = 'области';
$lang['up_update_districts'] = 'районы';
$lang['up_update_post_offices'] = ' города и пункты выдачи';
$lang['up_warehouses_data_info'] = 'Данные о точках выдачи';
$lang['up_description'] = 'Для корректной работы модуля нужно получить ключи от укрпочти, ввести их в соответствующие поля, нажать "обновить все", в случаи успеха выше будут три уведомления об обновлении областей, районов и городов вместе с пунктам выдачи.';
$lang['up_warehouses_data_update_warning'] = 'Обновление всех пунктов выдачи одновременно может вызвать большую нагрузку на сервер из-за чего данные могут не обновиться. Если у вас такое происходит, обновите данные о точках выдачи по частям, выбирая конкретные категории пунктов выдачи';
$lang['left_setting_up_title'] = 'Укр Почта';
$lang['settings_up'] = 'Настройки Укр Почты';
$lang['settings_up_api_token'] = 'Ключ API TOKEN';
$lang['settings_up_api_brearer'] = 'Ключ API BREARER';
$lang['settings_up_weight'] = 'Вес по умолчанию (кг)';
$lang['settings_up_volume'] = 'Объем по умолчанию (м<sup style="font-size: 8px;">3</sup>)';
$lang['settings_up_city'] = 'Город отправки';
$lang['settings_up_service_type'] = 'Технология доставки';
$lang['payment_method_up_cod'] = 'Наложенный платеж для Укрпочти';
$lang['product_up_volume'] = 'Объем м<sup style="font-size: 8px;">3</sup>';
$lang['order_up_term'] = 'Срок доставки (дней)';
$lang['order_up_calc'] = 'Пересчитать цену и сроки';
$lang['order_up_redelivery'] = 'Наложенный платеж';
$lang['order_up_region'] = 'Область';
$lang['order_up_district'] = 'Район';
$lang['order_up_office'] = 'Отделение';
$lang['order_up_city'] = 'Город';
$lang['order_up_street'] = 'Улица';
$lang['order_up_house'] = 'Дом';
$lang['order_up_apartment'] = 'Квартира';
$lang['order_up_warehouse'] = 'Пункт выдачи';
$lang['payment_up_cash_on_delivery'] = 'Разрешить оплату наложенным платежем';
$lang['payment_up_payment_method_name'] = 'Способ оплаты';
$lang['payment_up_cash_on_delivery_type'] = 'Список способов оплаты для оплаты наложенным платежом';
$lang['settings_up_up_auto_update_data'] = 'Автоматическое обновление пунктов выдачи';
$lang['settings_up_up_auto_update_data_title'] = 'Обновление кеша происходит в момент, когда пользователь запрашивает данные по пункту выдачи и при такой настройке когда кеш инвалидируется, один раз у одного пользователя может наблюдаться "подтормаживание" выбора города и отделения пока обновляется кеш.';
$lang['settings_up_cache_lifetime'] = 'Время жизни автоматического кеша (с)';
$lang['settings_up_last_update_cities'] = 'Дата обновления кеша городов';
$lang['settings_up_last_update_warehouses'] = 'Дата обновления кеша пунктов выдачи';
$lang['up_update_cache_now'] = 'Обновить';
$lang['up_update_address'] = 'Адрес обновлен успешно';
$lang['up_no_update_address'] = 'Поле адрес не обновлено, т.к. там уже содержатся данные';
$lang['up_update_address_info'] = 'Если вы хотите корректно обновить поле Адрес новыми данными, пожалуйста перед выбором отделения очистите поле Адрес находящееся выше.';
$lang['settings_up__description'] = 'Модуль Укрпочты позволяет в корзине выбирать город и отделение';
$lang['settings_up_options'] = 'Параметры';
$lang['tooltip_settings_up_api_token'] = 'Получить ключ API TOKEN вы можете в менеджера на сайте Укрпочта';
$lang['tooltip_settings_up_api_brearer'] = 'Получить ключ API BREARER вы можете в менеджера на сайте Укрпочта';
$lang['tooltip_settings_up_weight'] = 'Вес одного товара в заказе, который будет использоваться при просчете стоимости доставки';
$lang['tooltip_settings_up_volume'] = 'Объем одного товара в заказе, который будет использоваться при просчете стоимости доставки';
$lang['tooltip_settings_up_city'] = 'Город, из которого будут отправляться заказы из интернет-магазина';
$lang['settings_up_options_updating'] = 'Обновление данных';
$lang['settings_up_update_date_regions'] = 'Дата последнего обновления данных о областей:';
$lang['settings_up_update_date_districts'] = 'Дата последнего обновления данных о районов:';
$lang['settings_up_update_date_offices'] = 'Дата последнего обновления данных о городах и пунктах выдачи:';
$lang['settings_up_update_label'] = 'Для ускорения работы сайта, данные о пунктах выдачи Укрпочты хранятся в базе данных сайта.<br> Чтобы обновить данные о пунктах выдачи, нажмите на кнопку "Обновить".';
$lang['up_cron_update_cache_1'] = 'Для того, чтобы данные обновлялись автоматически, вам следует убедится, что на сервере настроено выполнение с помощью cron системного планировщика задач:';
$lang['up_cron_update_cache_2'] = 'каждую минуту (* * * * *) и данные будут обновляться автоматически раз в сутки.';
$lang['settings_up_include_volume'] = 'Включить в расчет стоимости доставки объем груза.';
$lang['settings_up_include_assessed'] = 'Включить в расчет стоимости доставки оценочную стоимость';
$lang['up_ukrpostha_empty_results'] = 'Извините, нет подходящих результатов.';