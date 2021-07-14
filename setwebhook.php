<?php

    include('vendor/autoload.php'); //Подключаем библиотеки
    include('core/init.php'); //Подключаем обработчик


    $bot = new \TelegramBot\Api\Client(BOT_TOKEN);

    //Установка WebHook
    try {
        $webhook = $bot->setWebHook(DOMAIN."/bot.php",CERTIFICATE);
        echo $webhook;
    } catch (TelegramBot\Api\HttpException $e) {
        // error
        echo "false";
    }