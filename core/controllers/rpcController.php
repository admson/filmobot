<?php

    // Таблица возможных сценариев у бота7
    // key = название меню, name = название в крошках, answer = ответ юзеру,
    // inline_keyboard = клавиатура, clean_cache (очистка предыдущих действий)
    // message = Функция обработки сообщения
    $routes = [
        "admin" => [
            'name' => "админка",
            'answer' => $lang['admin_answer'],
            'inline_keyboard' => [
                array(array('text'=> $lang['add_film'],'callback_data' => "view.add_film_text")),
                array(array('text'=> $lang['del_film'],'callback_data' => "view.categories")),
            ],
            'clean_cache' => true,
            'prev_menu' => false,
        ],

        "add_film_text" => [
            'name' => "добавить_текст",
            'answer' => $lang['add_film_answer'],
            'message' => "addFilmText",
            'prev_menu' => "admin",
        ],

        "add_film_photo" => [
            'name' => "добавить_фото",
            'answer' => $lang['add_film_photo_answer'],
            'inline_keyboard' => [
                array(array('text'=> $lang['skip'],'callback_data' => "view.add_film_trailer")),
            ],
            'message' => "addFilmPhoto",
            'prev_menu' => "add_film_text",
        ],

        "add_film_trailer" => [
            'name' => "добавить_трейлер",
            'answer' => $lang['add_film_trailer_answer'],
            'inline_keyboard' => [
                array(array('text'=> $lang['skip'],'callback_data' => "view.add_film_video")),
            ],
            'message' => "addFilmTrailer",
            'prev_menu' => "add_film_photo",
        ],

        "add_film_video" => [
            'name' => "добавить_фильм",
            'answer' => $lang['add_film_video_answer'],
            'message' => "addFilmVideo",
            'prev_menu' => "add_film_trailer",
        ],

        "categories" => [
            'name' => "список категорий",
            'answer' => $lang['choose_categorie'],
            'keyboard_func' => "menuCatalog",
        ],

    ];


    //  Функция которая рисует менюшки
    // $hash - либо хеш либо название menu
    // $chat_id - чат id аккаунит
    // inline_keyboard,$msg_id,$data($id фильма,категории и т.д) - не обязательные параметры
    function showRpc($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1) {
        global $routes,$db,$bot,$lang;

        // Если это хеш то получаем данные меню и id
        if (!isset($routes[$hash])) {
            $find_hash = $db->select("SELECT * FROM dialogs WHERE hash='$hash'");
            if (isset($find_hash[0]['id'])) {
//                $db->delete("DELETE FROM dialogs WHERE chat_id='".$find_hash[0]['chat_id']."' AND menu='".$find_hash[0]['menu']."'");
                $hash = $find_hash[0]['menu'];
                $data = $find_hash[0]['data'];
            }
        }
        // Если менюшка
        if (isset($routes[$hash])) {
            $state_hash = md5($hash.mt_rand(1111,9999));
            $bot_username = $bot->getMe()->getUsername();
            $db->update("UPDATE accounts SET menu='$hash' WHERE chat_id='$chat_id'");
            //Чистим историю и создаем новый хеш
            if (isset($routes[$hash]['clean_cache'])) $db->delete("DELETE FROM dialogs WHERE chat_id='$chat_id'");
            $new_state = $db->insert("INSERT INTO dialogs(hash,chat_id,menu) VALUES('$state_hash','".$chat_id."','".$hash."')");
            if (isset($data) && $data >= 1 && !isset($routes[$hash]['clean_cache'])) $db->update("UPDATE dialogs SET data='$data' WHERE id='$new_state'");

            // добавляем клавиатуру
            if (isset($routes[$hash]['inline_keyboard'])) {
                $kbarray = $routes[$hash]['inline_keyboard'];
            }else{
                $kbarray = [];
            }

            //Добавляем клавиатуру из функции
            if (isset($routes[$hash]['keyboard_func'])) {
                if (isset($page) && $page > 1) $db->update("UPDATE dialogs SET page='$page' WHERE id='$new_state'");
                $kbfunc = call_user_func($routes[$hash]['keyboard_func'],$page);
                $kbarray = array_merge($kbarray,$kbfunc);
            }

            // Хлебные крошки и кнопки Назад, отмена
            $breads = "";
            if (!isset($routes[$hash]['clean_cache'])) {
                $dialogs_bread = $db->select("SELECT * FROM dialogs WHERE chat_id='$chat_id' ORDER BY created_at");
                foreach ($dialogs_bread as $state) {
                    if (isset($routes[$state['menu']]) && !isset($routes[$state['menu']]['clean_cache'])) $breads.= "<i>/<a href='https://t.me/".$bot_username."?start=".$state['hash']."'>".$routes[$state['menu']]['name']."</a></i>";
                }
            }

            // Назад и отмена
            if (!isset($routes[$hash]['clean_cache'])) {
                $prev_menu = $routes[$hash]['prev_menu'];
                array_push($kbarray, array(array('text'=> $lang['back'],'callback_data' => "prew.".$prev_menu),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
            }


            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kbarray);

            // создаем ответ
            $answer = $routes[$hash]['answer'];
            if (!empty($breads)) $answer = $breads."\n\n".$routes[$hash]['answer'];

            if (isset($msg_id) && $msg_id > 0) {
                editMessage($bot,$chat_id,$msg_id,$answer,$keyboard);
            }else{
                sendMessage($bot,$chat_id,$answer,$keyboard );
            }
        }
    }