<?php

    include('vendor/autoload.php'); //Подключаем библиотеки
    include('core/init.php'); //Подключаем обработчик

    // мозги бота
    include 'core/controllers/bot/authController.php'; // Авторизация
    include 'core/controllers/bot/messageController.php'; // Контроллер для входящих сообщений
    include 'core/controllers/bot/callbackController.php'; // Контроллер для каллбеков
    include 'core/controllers/botController.php'; // Основные функции Bot api
    include 'core/controllers/entitiesController.php'; // Обработчик для enities

    $bot = new \TelegramBot\Api\Client(BOT_TOKEN);

    $bot->command('start', function ($message) use ($bot, $db, $dbconnection, $lang, $employers) {
        $text = $message->getText();
        $firstname = $message->getChat()->getFirstName();
        $last_name = $message->getChat()->getLastName();
        $username = $message->getChat()->getUsername();
        $chat_id = $message->getChat()->getId();

        // Проверка существует ли аккаунт
        $is_account = $db->select("SELECT * FROM accounts WHERE chat_id='$chat_id'");
        if (!isset($is_account[0]['id'])) {
            $new_account = true;
        }else{
            // status 2 это если человек заблокирован
            if ($is_account[0]['status'] == 2) exit();
        }
        // Авторизация пользователя
        $user = new authController($chat_id, $username, $firstname, $last_name);
        $user_data = $user->authUser();

        $msg_hash = explode(" ", $text);
        #Check hash
        if (isset($msg_hash[1])) {
            $prefix = substr($msg_hash[1],0,2);
            if ($prefix == "flm") {

            }else{
                showRpc($msg_hash[1], $chat_id);
            }
        }else{
            if (isset($employers[$chat_id])) {
                $role = $employers[$chat_id];
            }else{
                $role = "main";
            }
            showRpc(strtolower($role), $chat_id);
        }
    });

    // Обработчик для обновлений
    $bot->on(function ($Update) use ($bot,$db,$dbconnection,$lang) {
        try {
            $Message = $Update->getMessage();
        } catch (TelegramBot\Api\HttpException $e) {
            // todo
        }

        try {
            $Callback = $Update->getCallbackQuery();
        } catch (TelegramBot\Api\HttpException $e) {
            // todo
        }

        // Message Controller
        if (isset($Message) && !is_null($Message)) {
            $controller_m = new MessageController($bot, $db, $dbconnection, $Message, $lang);
            return true;
        }

        // Callback Controller
        if (isset($Callback) && !is_null($Callback)) {
            $controller_q = new callbackController($bot, $db, $dbconnection, $Callback, $lang);
        }
    }, function () {
        return true;
    });

    $bot->run();