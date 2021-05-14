<?php

    // Настройки подключения к DB MySQL
    define('DB_SERVER','localhost'); // Сервер БД
    define('DB_NAME','filmbot'); // Название БД
    define('DB_PASSWORD','H5c5Z3a6'); // Пароль БД
    define('DB_USERNAME','filmbot'); // Пользователь БД

    // Настройки бота
    define('BOT_TOKEN','1718673256:AAEBwFJOV3bIQz3ENGkQqnzp8pCL7-H3SnI'); // API Token бота
    define('LANG','ru'); // Стандартный язык
    define('PER_PAGE', 3 ); // Кол-во строчек на страницу
    define('PER_PAGE_COL', 2 ); // Кнопок колонок на страницу
    define('SESSION_TIMEOUT', 120); // таймаут для удаления активностей (в минутах)
    define('FILM_TIMEOUT', 30); // таймаут для удаления фильмов ( в минутах)

    //Настройки чатов
    // * Бот должен быть добавлен в канал как администратор
    define('PRIVATECHATID', -1001165241074 ); // ID/Link приватного канала для публикаций. (false/chat_id)
    define('PUBLICCHATID', "@tesctchanneks"); // ID/Link публичного канала для публикаций. (false/chat_id/chatlink(@demochannel))

    //Возможные сюжеты
    $scripts = ['admin','main'];

    // Chat_id => "Role"
    $employers = [
        920538735 => "Admin",
        684000481 => "Admin",
        517019519 => "Admin"
    ];