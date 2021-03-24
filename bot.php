<?php

    include('vendor/autoload.php'); //Подключаем библиотеки
    include('core/init.php'); //Подключаем обработчик

    // мозги бота
    include 'core/controllers/bot/authController.php'; // Авторизация
    include 'core/controllers/bot/messageController.php'; // Контроллер для входящих сообщений
    include 'core/controllers/bot/callbackController.php'; // Контроллер для каллбеков
    include 'core/controllers/bot/viewsController.php'; // Вьюшки/Диалоги
    include 'core/controllers/botController.php'; // Основные функции Bot api
    include 'core/controllers/entitiesController.php'; // Обработчик для enities

    $bot = new \TelegramBot\Api\Client(BOT_TOKEN);

    $bot->command('start', function ($message) use ($bot, $db, $dbconnection, $lang, $admins) {
        $text = $message->getText();
        $firstname = $message->getChat()->getFirstName();
        $last_name = $message->getChat()->getLastName();
        $username = $message->getChat()->getUsername();
        $chat_id = $message->getChat()->getId();
        $lng = getLang($message->getFrom()->getLanguageCode()); // Получаем язык

        // Проверка существует ли аккаунт
        $is_account = $db->select("SELECT * FROM accounts WHERE chat_id='$chat_id'");
        if (!isset($is_account[0]['id'])) {
            $new_account = true;
        }else{
            // status 2 это если человек заблокирован
            if ($is_account[0]['status'] == 2) {
                exit();
            }
        }
        // Авторизация пользователя
        $user = new authController($chat_id, $username, $firstname, $last_name);
        $user_data = $user->authUser();
        $db->update("UPDATE accounts SET banned=0,lang='".LANG."' WHERE chat_id='$chat_id'");
        $user = new authController($chat_id, $username, $firstname, $last_name);
        $user_data = $user->authUser();
        // ViewContoller
        $view = new viewController($bot, $user_data, false, $db, $dbconnection, $lang);

        if (in_array($chat_id,$admins)) {
            $view->menuAdmin();
        }else{
            $view->menuMain();
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

        // Message Controller передаем туда инфу и язык
        if (isset($Message) && !is_null($Message)) {
            $controller_m = new MessageController($bot, $db, $dbconnection, $Message, $lang);
        }

        // Callback Controller
        if (isset($Callback) && !is_null($Callback)) {
            $controller_q = new callbackController($bot, $db, $dbconnection, $Callback, $lang);
        }
    }, function () {
        return true;
    });

    $bot->run();