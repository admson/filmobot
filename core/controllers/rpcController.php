<?php

    //  Функция которая рисует менюшки
    // $hash - либо хеш либо название menu
    // $chat_id - чат id аккаунит
    // inline_keyboard,$msg_id,$data($id фильма,категории и т.д) - не обязательные параметры
    function showRpc($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1) {
        global $routes,$db,$bot,$lang;
        // Удаление по таймауту (фильмы и активность)
        $now_time = new DateTime('now');
        $now_time->modify("-".SESSION_TIMEOUT." hour");
        $date = $now_time->format('Y-m-d H:i:s');
        $db->delete("DELETE FROM dialogs WHERE created_at <= '$date'");
        $now_time = new DateTime('now');
        $now_time->modify("-".FILM_TIMEOUT." hour");
        $timeout = $now_time->format('Y-m-d H:i:s');
        $db->delete("DELETE FROM films WHERE created_at <= '$timeout' AND hash IS NULL");
        // Если это хеш то получаем данные меню и id
        if (!isset($routes[$hash])) {
            $find_hash = $db->select("SELECT * FROM dialogs WHERE hash='$hash'");
            if (isset($find_hash[0]['id'])) {
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