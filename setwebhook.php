<?php

    include('vendor/autoload.php'); //Подключаем библиотеки
    include('core/init.php'); //Подключаем обработчик


    $bot = new \TelegramBot\Api\Client(BOT_TOKEN);

    //Установка WebHook
    try {
        if (CERTIFICATE) $webhook = $bot->setWebHook(DOMAIN."/bot/bot.php",new \CURLFile(realpath(CERTIFICATE)));
        if (!CERTIFICATE) $webhook = $bot->setWebHook(DOMAIN."/bot/bot.php");
        echo $webhook;
    } catch (TelegramBot\Api\HttpException $e) {
        // error
        echo "false";
    }