<?php

    // Таблица диалогов для админки
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


    // Функция получения хеш-тегов
    function getHashtags($string) {
        $hashtags= FALSE;
        preg_match_all("/(#\w+)/u", $string, $matches);
        if ($matches) {
            $hashtagsArray = array_count_values($matches[0]);
            $hashtags = array_keys($hashtagsArray);
        }
        return $hashtags;
    }


    // Проверка и парсинг текста
    function addFilmText($data) {
        global $db,$dbconnection,$bot,$lang;
        if (isset($data['text'])) {
            $kb = [];
            array_push($kb, array(array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
            $string = explode("\n", $data['text']);
            // получение название и года
            if (isset($string[0])) {
                preg_match('#\\((.*?)\\)#', $string[0], $year); // год
                $start = 0;
                $end = strpos($string[0], '(', $start + 1);
                $length = $end - $start;
                $name = substr($string[0], 0, $length - 1);
                $name = trim($name); // название фильма
            }


            // Получаем жанры и вносим в базу данных
            $genres = getHashtags($data['text']);
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

            // Проверка на формат, insert в бд
            if (isset($year[1]) && isset($name) && count($ctgrs) >= 1) {
                $new_film = $db->insert("INSERT INTO films(text,name,year,categories) VALUES('".mysqli_real_escape_string($dbconnection,$data['text'])."','$name','".$year[1]."','".json_encode($ctgrs)."')");
                if (isset($new_film)) {
                    showRpc("add_film_photo", $data['chat_id'], false, false, $new_film);
                }
            }else{
                sendMessage($bot, $data['chat_id'], $lang['wrong_format'], $keyboard);
            }
        }
    }


    // Добавляем фотку к фильму
    function addFilmPhoto($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['photo'])) {
                // Получаем id файла на серверах tg и меняем в фильме
                $orig_file = $data['photo'][array_key_last($data['photo'])]->getFileId();
                $db->update("UPDATE films SET photo='".$orig_file."' WHERE id='$film_id'");
                showRpc("add_film_trailer", $data['chat_id'], false, false, $film_id);
            }else{
                // Ошибка если что-то не так
                $kb = [];
                array_push($kb, array(array('text'=> $lang['back'],'callback_data' => "view.add_film_text"),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                sendMessage($bot, $data['chat_id'], $lang['error_need_photo'], $keyboard);
            }
        }
    }


    // Добавляем трейлер фильму
    function addFilmTrailer($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            // id фильма
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


    // Добавляем сам фильм
    function addFilmVideo($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['video'])) {
                // Получаем chat_id  и обновляем дб
                $orig_file = $data['video']->getFileId();
                $db->update("UPDATE films SET video='$orig_file' WHERE id='$film_id'");
                $db->update("UPDATE films SET hash='".md5($film_id.mt_rand(1111,9999))."' WHERE id='$film_id'");
                // сообщение об успешном создании фильма.
                sendMessage($bot, $data['chat_id'], $lang['success_create']);
                // Отправка сообщений в каналы
                sendToChats($film_id);
                // Показываем главное меню
                showRpc("admin", $data['chat_id'], false, false, $film_id);
            }else{
                $kb = [];
                array_push($kb, array(array('text'=> $lang['back'],'callback_data' => "view.add_film_trailer"),array('text'=> $lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                sendMessage($bot, $data['chat_id'], $lang['error_need_video'], $keyboard);
            }
        }
    }


    //Функция отправки фотографий и кнопок по каналам
    function sendToChats($film_id) {
        global $db,$bot,$lang;
        $film = $db->select("SELECT * FROM films WHERE id='$film_id'");

        // Публичный канал
        if (PUBLICCHATID != false) {
            // Фото в чат
            if (isset($film[0]['photo'])) {
                $p1_msg_id = sendPhoto($bot,PUBLICCHATID,$film[0]['photo'],$film[0]['text']);
            }
            // Трейлер с кнопкой перейти к просмотру
            if (isset($film[0]['trailer'])) {
                $kb = [];
                $bot_username = $bot->getMe()->getUsername();
                array_push($kb, array(array('text'=> $lang['gotofilm'],'url' => "https://t.me/".$bot_username."?start=flm".$film[0]['hash'])));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                $p2_msg_id = sendVideo($bot,PUBLICCHATID,$film[0]['trailer'],false,$keyboard);
            }

            if (isset($p1_msg_id)) $db->update("UPDATE films SET public_msg_id='".$p1_msg_id->getMessageId()."' WHERE id='$film_id'");
            if (isset($p2_msg_id)) $db->update("UPDATE films SET public2_msg_id='".$p2_msg_id->getMessageId()."' WHERE id='$film_id'");
        }


        // Приватный канал, отправка медиагруппы
        if (PRIVATECHATID != false) {
            $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
            if (isset($film[0]['photo'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto($film[0]['photo'],$film[0]['text']));
            }
            if (isset($film[0]['trailer'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['trailer']));
            }
            if (isset($film[0]['video'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['video']));
            }

            try {
                $msg_id2 = $bot->sendMediaGroup(PRIVATECHATID,$media);
                if (isset($msg_id2)) $db->update("UPDATE films SET private_msg_id='".$msg_id2[0]->getMessageId()."' WHERE id='$film_id'");
            } catch (TelegramBot\Api\HttpException $e) {
                exit();
            }
        }


    }