<?php

    $routes = [
        "admin" => [
            'name' => "админка",
            'answer' => $lang['admin_answer'],
            'inline_keyboard' => [
                array(array('text'=> $lang['add_film'],'callback_data' => "view.add_film_text")),
                array(array('text'=> $lang['del_film'],'callback_data' => "view.del_film")),
            ],
            'clean_cache' => true,
        ],

        "add_film_text" => [
            'name' => "добавить_текст",
            'answer' => $lang['add_film_answer'],
            'message' => "addFilmText",
        ],

        "add_film_photo" => [
            'name' => "добавить_фото",
            'answer' => $lang['add_film_photo_answer'],
            'message' => "addFilmPhoto",
        ],

        "add_film_trailer" => [
            'name' => "добавить_трейлер",
            'answer' => $lang['add_film_trailer_answer'],
            'message' => "addFilmTrailer",
        ],

        "add_film_video" => [
            'name' => "добавить_фильм",
            'answer' => $lang['add_film_video_answer'],
            'message' => "addFilmVideo",
        ],


    ];

    function showRcp($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false) {
        global $routes,$db,$bot,$lang;
        if (!isset($routes[$hash])) {
            $find_hash = $db->select("SELECT * FROM dialogs WHERE hash='$hash'");
            if (isset($find_hash[0]['id'])) {
//                $db->delete("DELETE FROM dialogs WHERE chat_id='".$find_hash[0]['chat_id']."' AND menu='".$find_hash[0]['menu']."'");
                $hash = $find_hash[0]['menu'];
                $data = $find_hash[0]['data'];
            }
        }
        if (isset($routes[$hash])) {
            $state_hash = md5($hash.mt_rand(1111,9999));
            $bot_username = $bot->getMe()->getUsername();
            $db->update("UPDATE accounts SET menu='$hash' WHERE chat_id='$chat_id'");
            if (isset($routes[$hash]['clean_cache'])) $db->delete("DELETE FROM dialogs WHERE chat_id='$chat_id'");
            $new_state = $db->insert("INSERT INTO dialogs(hash,chat_id,menu) VALUES('$state_hash','".$chat_id."','".$hash."')");
            if (isset($data) && $data >= 1) $db->update("UPDATE dialogs SET data='$data' WHERE id='$new_state'");


            if (isset($routes[$hash]['inline_keyboard'])) {
                $kbarray = $routes[$hash]['inline_keyboard'];
            }else{
                $kbarray = [];
            }

            $breads = "";

            if (!isset($routes[$hash]['clean_cache'])) {
                $dialogs = $db->select("SELECT * FROM dialogs WHERE chat_id='$chat_id' ORDER BY created_at DESC");
                $dialogs_bread = $db->select("SELECT * FROM dialogs WHERE chat_id='$chat_id' ORDER BY created_at");

                if (isset($dialogs[1]['id']) && isset($routes[$dialogs[1]['menu']])) {
                    $prev_menu = $dialogs[1]['id'];
                    if (isset($routes[$dialogs[1]['menu']]['clean_cache'])) {
                        array_push($kbarray, array(array('text'=> $lang['cancel'],'callback_data' => "hash.".$prev_menu)));
                    }else{
                        array_push($kbarray, array(array('text'=> $lang['back'],'callback_data' => "hash.".$prev_menu)));
                    }
                }

                foreach ($dialogs_bread as $state) {
                    if (isset($routes[$state['menu']])) $breads.= "<i>/<a href='https://t.me/".$bot_username."?start=".$state['hash']."'>".$routes[$state['menu']]['name']."</a></i>";
                }
            }


            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kbarray);

            $answer = $routes[$hash]['answer'];
            if (!empty($breads)) $answer = $breads."\n\n".$routes[$hash]['answer'];

            if (isset($msg_id) && $msg_id > 0) {
                editMessage($bot,$chat_id,$msg_id,$answer,$keyboard);
            }else{
                sendMessage($bot,$chat_id,$answer,$keyboard );
            }
        }
    }