<?php

    // Настройки подключения к DB MySQL
    define('DB_SERVER','localhost'); // Сервер БД
    define('DB_NAME',''); // Название БД
    define('DB_PASSWORD',''); // Пароль БД
    define('DB_USERNAME',''); // Пользователь БД

    // Настройки бота
    define('BOT_TOKEN','token'); // API Token бота
    define('LANG','ru'); // Стандартный язык
    define('PER_PAGE', 3 ); // Кнопок на страницу.

    //Настройки чатов
    // * Бот должен быть добавлен в канал как администратор
    define('PRIVATECHATID',  false); // ID/Link приватного канала для публикаций. (false/chat_id)
    define('PUBLICCHATID', false); // ID/Link публичного канала для публикаций. (false/chat_id/chatlink(@demochannel))

    $admins = [
        // chat_id,

    ];