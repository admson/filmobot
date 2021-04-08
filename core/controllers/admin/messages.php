<?php
    
    function addFilmText($data) {
        global $db,$bot,$lang;
        if (isset($data['text'])) {
            $kb = [];
            array_push($kb, array(array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
            $string = explode("\n", $data['text']);
            if (isset($string[0]) && isset($string[1])) {
                preg_match('#\\((.*?)\\)#', $string[0], $year);
                $start  = strpos($string[0], ',');
                $end    = strpos($string[0], '(', $start + 1);
                $length = $end - $start;
                $name = substr($string[0], $start + 1, $length - 1);
                $name = trim($name);

                $genres = preg_split('/[\s]+/', $string[1]);
                $ctgrs = [];
                foreach ($genres as $gen) {
                    $gen = str_replace("#", "", $gen);
                    if (isset($gen) && !empty($gen)) {
                        $db_gen = $db->select("SELECT * FROM categories WHERE name='$gen'");
                        if (!isset($db_gen[0]['id'])) {
                            $id = $db->insert("INSERT INTO categories(name) VALUES('$gen')");
                        }else{
                            $id = $db_gen[0]['id'];
                        }
                        array_push($ctgrs,$id);
                    }
                }

                if (isset($year[1]) && isset($name) && count($ctgrs) >= 1) {
                    $new_film = $db->insert("INSERT INTO films(text,name,year,categories) VALUES('".$data['text']."','$name','".$year[1]."','".json_encode($ctgrs)."')");
                    if (isset($new_film)) {
                        showRpc("add_film_photo", $data['chat_id'], false, false, $new_film);
                    }
                }else{
                    sendMessage($bot, $data['chat_id'], $lang['wrong_format'], $keyboard);
                }
            }else{
                sendMessage($bot, $data['chat_id'], $lang['wrong_format'], $keyboard);
            }
        }
    }

    function addFilmPhoto($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['photo'])) {
                $orig_file = $data['photo'][array_key_last($data['photo'])]->getFileId();
                $db->update("UPDATE films SET photo='".$orig_file."' WHERE id='$film_id'");
                showRpc("add_film_trailer", $data['chat_id'], false, false, $film_id);
            }else{
                $kb = [];
                array_push($kb, array(array('text'=> $lang['back'],'callback_data' => "view.add_film_text"),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                sendMessage($bot, $data['chat_id'], $lang['error_need_photo'], $keyboard);
            }
        }
    }

    function addFilmTrailer($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['video'])) {
                $orig_file = $data['video']->getFileId();
                $db->update("UPDATE films SET trailer='$orig_file' WHERE id='$film_id'");
                showRpc("add_film_video", $data['chat_id'], false, false, $film_id);
            }else{
                $kb = [];
                array_push($kb, array(array('text'=> $lang['back'],'callback_data' => "view.add_film_photo"),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                sendMessage($bot, $data['chat_id'], $lang['error_need_trailer'], $keyboard);
            }
        }
    }

    function addFilmVideo($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['video'])) {
                $orig_file = $data['video']->getFileId();
                $db->update("UPDATE films SET video='$orig_file' WHERE id='$film_id'");
                sendMessage($bot, $data['chat_id'], $lang['success_create']);
                sendToChats($film_id);
                showRpc("admin", $data['chat_id'], false, false, $film_id);
            }else{
                $kb = [];
                array_push($kb, array(array('text'=> $lang['back'],'callback_data' => "view.add_film_trailer"),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                sendMessage($bot, $data['chat_id'], $lang['error_need_video'], $keyboard);
            }
        }
    }

    function sendToChats($film_id) {
        global $db,$bot,$lang;
        $film = $db->select("SELECT * FROM films WHERE id='$film_id'");

        $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
        //
        if (isset($film[0]['photo'])) {
            $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto($film[0]['photo'],$film[0]['text']));
        }
        if (isset($film[0]['trailer'])) {
            $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['trailer']));
        }

        if (PUBLICCHATID != false) {
            try {
                $msg_id1 = $bot->sendMediaGroup(PUBLICCHATID,$media);
                if (isset($msg_id1)) $db->update("UPDATE films SET public_msg_id='".$msg_id1->getMessageId()."' WHERE id='$film_id'");
            } catch (TelegramBot\Api\HttpException $e) {
                exit();
            }
        }

        if (PRIVATECHATID != false) {
            if (isset($film[0]['video'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['video']));
            }

            try {
                $msg_id2 = $bot->sendMediaGroup(PRIVATECHATID,$media);
                if (isset($msg_id1)) $db->update("UPDATE films SET private_msg_id='".$msg_id2->getMessageId()."' WHERE id='$film_id'");
            } catch (TelegramBot\Api\HttpException $e) {
                exit();
            }
        }


    }