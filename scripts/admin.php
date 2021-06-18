<?php

    // Таблица диалогов для админки
    // key = название меню, name = название в крошках, answer = ответ юзеру,
    // inline_keyboard = клавиатура, clean_cache (очистка предыдущих действий)
    // message = Функция обработки сообщения
    // keyboard_func = функция для обработки клавиатуры в сюжете
    // callback_menu = используется в getbuttons перекидывает на меню из каталога

    class Admin extends scriptController {
        // Берем данные с scriptController
        // $this->bot - бот, $this->db - база данных, $this->lang - язык

        public function __construct(){
            parent::__construct();

            $this->routes = [
                "admin" => [
                    'name' => "админка",
                    'answer' => $this->lang['admin_answer'],
                    'inline_keyboard' => [
                        array(array('text'=> $this->lang['add_film'],'callback_data' => "view.add_film_text")),
                        array(array('text'=> $this->lang['del_film'],'callback_data' => "view.categories")),
                        array(array('text'=> $this->lang['category_images'],'callback_data' => "view.category_images")),
                    ],
                    'clean_cache' => true,
                    'prev_menu' => false,
                ],

                "add_film_text" => [
                    'name' => "добавить_текст",
                    'answer' => $this->lang['add_film_answer'],
                    'message' => "addFilmText",
                    'prev_menu' => "admin",
                ],

                "add_film_photo" => [
                    'name' => "добавить_фото",
                    'answer' => $this->lang['add_film_photo_answer'],
                    'inline_keyboard' => [
                        array(array('text'=> $this->lang['skip'],'callback_data' => "view.add_film_trailer")),
                    ],
                    'message' => "addFilmPhoto",
                    'prev_menu' => "add_film_text",
                ],

                "add_film_trailer" => [
                    'name' => "добавить_трейлер",
                    'answer' => $this->lang['add_film_trailer_answer'],
                    'inline_keyboard' => [
                        array(array('text'=> $this->lang['skip'],'callback_data' => "view.add_film_video")),
                    ],
                    'message' => "addFilmTrailer",
                    'prev_menu' => "add_film_photo",
                ],

                "add_film_video" => [
                    'name' => "добавить_фильм",
                    'answer' => $this->lang['add_film_video_answer'],
                    'message' => "addFilmVideo",
                    'prev_menu' => "add_film_trailer",
                ],

                "categories" => [
                    'name' => "список категорий",
                    'answer' => $this->lang['choose_categories'],
                    'keyboard_func' => "getCategories",
                    'callback_menu' => "films", // Меню после нажатия кнопки (id)
                    'prev_menu' => "admin",
                ],

                "films" => [
                    'name' => "список фильмов",
                    'answer' => $this->lang['choose_film'],
                    'keyboard_func' => "getFilms",
                    'callback_menu' => "film", // getFilms перекинет на film
                    'prev_menu' => "categories",
                ],

                "film" => [
                    'name' => "карточка фильма",
                    'view_func' => 'showFilm', // Функция вывода фильма
                    'prev_menu' => "films",
                    'delete_btn' => "films" // Создает кнопку удаления которая удалит из таблицы films (можно добавить в любое меню)
                ],

                //При нажатии на кнопку "О компании"
                "about_company" => [
                    'name' => "О компании",
                    'answer' => $this->lang['about_company_text'],
                    'prev_menu' => false,
                ],

                //При нажатии на кнопку "Статистика"
                "statistics" => [
                    'name' => "Статистика",
                    'view_func' => 'showStats', // Функция вывода статистики
                    'prev_menu' => false,
                ],

                //При нажатии на кнопку "Картинки к категориям"
                "category_images" => [
                    'name' => "Картинки к категориям",
                    'answer' => $this->lang['category_images_choose'],
                    'keyboard_func' => "getCategories",
                    'callback_menu' => "edit_category_image", // Меню после нажатия кнопки (id)
                    'prev_menu' => "admin",
                ],

                "edit_category_image" => [
                    'name' => "изменить картинку категории",
                    'answer' => $this->lang['category_images_edit'],
                    'view_func' => 'editCategory',
                    'prev_menu' => "category_images",
                    'message' => "addCategoryImage",
                ],

            ];
        }

        // Обработка хешей
        public function customHash($hash) {
            $find_film = $this->db->select("SELECT * FROM films WHERE hash='$hash'");
            if (isset($find_film[0]['id'])) {
                $id = $find_film[0]['id'];
                return ['film',$id];
            }
        }

        //Обработка каллбеков для сюжета
        public function callbacks($call,$chat_id,$msg_id = false) {
            $rpc = new Rpc();

            if ($call[0] == "delete" && isset($call[1])) {
                //Удаляем фильм
                $this->db->delete("DELETE FROM films WHERE id='".$call[1]."'");
                //Прказываем prewMenu
                $dialog = $this->db->select("SELECT * FROM _dialogs WHERE chat_id='".$chat_id."' ORDER BY created_at DESC LIMIT 2");
                $rpc->prewMenu($chat_id,$dialog[1]['menu'],$msg_id);
            }
            if ($call[0] == "edit_category_image" && isset($call[1])) {
                //Удаляем фильм
                $this->db->delete("DELETE FROM films WHERE id='".$call[1]."'");
                //Прказываем prewMenu
                $dialog = $this->db->select("SELECT * FROM _dialogs WHERE chat_id='".$chat_id."' ORDER BY created_at DESC LIMIT 2");
                $rpc->prewMenu($chat_id,$dialog[1]['menu'],$msg_id);
            }
        }

        // Проверка и парсинг текста
        public function addFilmText($data) {
            if (isset($data['text'])) {
                $kb = [];
                array_push($kb, array(array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                $string = explode("\n", $data['text']);
                // получение название и года
                if (isset($string[0])) {
                    preg_match('#\\((.*?)\\)#', $string[0], $year); // год
                    if (isset($year[1]) && intval($year[1]) >= 1) $year_val = intval($year[1]);
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
                        $db_gen = $this->db->select("SELECT * FROM categories WHERE name='$gen'");
                        if (!isset($db_gen[0]['id'])) {
                            $id = $this->db->insert("INSERT INTO categories(name) VALUES('$gen')");
                        }else{
                            $id = $db_gen[0]['id'];
                        }
                        array_push($ctgrs,$id);
                    }
                }

                // Проверка на формат, insert в бд
                if (isset($year_val) && isset($name) && count($ctgrs) >= 1) {
                    $new_film = $this->db->insert("INSERT INTO films(text,name,year,categories) VALUES('".mysqli_real_escape_string($this->dbconnection,$data['text'])."','$name','".$year_val."','".json_encode($ctgrs)."')");
                    if (isset($new_film)) {
                        showRpc("add_film_photo", $data['chat_id'], false, false, $new_film);
                    }
                }else{
                    sendMessage($this->bot, $data['chat_id'], $this->lang['wrong_format'], $keyboard);
                }
            }
        }

        // Добавляем фото к фильму
        public function addFilmPhoto($data) {
            if (isset($data['dialog']['data'])) {
                $film_id = $data['dialog']['data'];
                if (isset($data['photo'])) {
                    // Получаем id файла на серверах tg и меняем в фильме
                    $orig_file = $data['photo'][array_key_last($data['photo'])]->getFileId();
                    $this->db->update("UPDATE films SET photo='".$orig_file."' WHERE id='$film_id'");
                    showRpc("add_film_trailer", $data['chat_id'], false, false, $film_id);
                }else{
                    // Ошибка если что-то не так
                    $kb = [];
                    array_push($kb, array(array('text'=> $this->lang['back'],'callback_data' => "view.add_film_text"),array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                    sendMessage($this->bot, $data['chat_id'], $this->lang['error_need_photo'], $keyboard);
                }
            }
        }

        // Добавляем трейлер фильму
        public function addFilmTrailer($data) {
            if (isset($data['dialog']['data'])) {
                // id фильма
                $film_id = $data['dialog']['data'];
                if (isset($data['video'])) {
                    $orig_file = $data['video']->getFileId();
                    $this->db->update("UPDATE films SET trailer='$orig_file' WHERE id='$film_id'");
                    showRpc("add_film_video", $data['chat_id'], false, false, $film_id);
                }else{
                    $kb = [];
                    array_push($kb, array(array('text'=> $this->lang['back'],'callback_data' => "view.add_film_photo"),array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                    sendMessage($this->bot, $data['chat_id'], $this->lang['error_need_trailer'], $keyboard);
                }
            }
        }

        // Добавляем сам фильм
        public function addFilmVideo($data) {
            if (isset($data['dialog']['data'])) {
                $film_id = $data['dialog']['data'];
                if (isset($data['video'])) {
                    // Получаем chat_id  и обновляем дб
                    $orig_file = $data['video']->getFileId();
                    $this->db->update("UPDATE films SET video='$orig_file' WHERE id='$film_id'");
                    $this->db->update("UPDATE films SET hash='".md5($film_id.mt_rand(1111,9999))."' WHERE id='$film_id'");
                    // сообщение об успешном создании фильма.
                    sendMessage($this->bot, $data['chat_id'], $this->lang['success_create']);
                    // Отправка сообщений в каналы
                    self::sendToChats($film_id);
                    // Показываем главное меню
                    showRpc("admin", $data['chat_id'], false, false, $film_id);
                }else{
                    $kb = [];
                    array_push($kb, array(array('text'=> $this->lang['back'],'callback_data' => "view.add_film_trailer"),array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                    sendMessage($this->bot, $data['chat_id'], $this->lang['error_need_video'], $keyboard);
                }
            }
        }

        //Функция отправки фотографий и кнопок по каналам
        function sendToChats($film_id) {
            $film = $this->db->select("SELECT * FROM films WHERE id='$film_id'");

            // Публичный канал
            if (PUBLICCHATID != false) {
                // Фото в чат
                if (isset($film[0]['photo'])) {
                    $p1_msg_id = sendPhoto($this->bot,PUBLICCHATID,$film[0]['photo'],$film[0]['text']);
                }
                // Трейлер с кнопкой перейти к просмотру
                if (isset($film[0]['trailer'])) {
                    $kb = [];
                    $bot_username = $this->bot->getMe()->getUsername();
                    array_push($kb, array(array('text'=> $this->lang['gotofilm'],'url' => "https://t.me/".$bot_username."?start=".$film[0]['hash'])));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                    $p2_msg_id = sendVideo($this->bot,PUBLICCHATID,$film[0]['trailer'],false,$keyboard);
                }

                if (isset($p1_msg_id)) $this->db->update("UPDATE films SET public_msg_id='".$p1_msg_id->getMessageId()."' WHERE id='$film_id'");
                if (isset($p2_msg_id)) $this->db->update("UPDATE films SET public2_msg_id='".$p2_msg_id->getMessageId()."' WHERE id='$film_id'");
            }


            // Приватный канал, отправка медиагруппы
            if (PRIVATECHATID != false) {
                $media = createMediaGroup($film,true);
                try {
                    $msg_id2 = $this->bot->sendMediaGroup(PRIVATECHATID,$media);
                    if (isset($msg_id2)) $this->db->update("UPDATE films SET private_msg_id='".$msg_id2[0]->getMessageId()."' WHERE id='$film_id'");
                } catch (TelegramBot\Api\HttpException $e) {
                    exit();
                }
            }


        }

        //Получение категорий для удаления фильмов
        public function getCategories($page, $data = false) {
            $content = $this->db->select("SELECT * FROM categories"); // Получаем категории
            $keyboard = scriptController::getButtons($page,$content); // Показываем кнопки
            return $keyboard;
        }

        //Получение фильмов
        public function getFilms($page,$ctgr_id = false) {
            $content = $this->db->select("SELECT * FROM films");
            $films = self::filterByCategory($content,$ctgr_id);
            $keyboard = scriptController::getButtons($page,$films);
            return $keyboard;
        }

        // Функция фильтрации фильмов в зависимости от категории
        public function filterByCategory($array,$id) {
            $content = [];
            foreach ($array as $film) {
                $categories = json_decode($film['categories']);
                foreach ($categories as $ctgr) {
                    if (intval($id) == intval($ctgr)) {
                        array_push($content,$film);
                    }
                }
            }
            return $content;
        }

        // Функция показа фильма
        public function showFilm($id,$chat_id,$breads,$keyboard) {
            // Получаем фильм
            $film = $this->db->select("SELECT * FROM films WHERE id='$id'");
            // Добавляем ссылку посмотреть
            $bot_username = $this->bot->getMe()->getUsername();
            $film[0]['text'] = substr($film[0]['text'], 0,880);
            $film[0]['text'].= "...";
            $film[0]['text'].= "\n\n<a href='https://t.me/".$bot_username."?start=".$film[0]['hash']."'>".$this->lang['show_film_button']."</a>";

            // Отправляем медиа
            $media = createMediaGroup($film,true);
            sendMediaGroup($this->bot,$chat_id,$media);

            //Хлебные крошки
            $answer = $breads."\n\n".$film[0]['name']." (".$film[0]['year'].")";

            //Отправляем ролик с кнопками
            sendVideo($this->bot,$chat_id,$film[0]['video'],$answer,$keyboard);
        }

        // Функция редактирования категорию
        public function editCategory($id,$chat_id,$breads,$keyboard) {
            // Получаем категорию
            $ctgr = $this->db->select("SELECT * FROM categories WHERE id='$id'");

            //Хлебные крошки и answer
            $answer = $breads."\n\n".$this->lang['category'].": ".$ctgr[0]['name']."\n".$this->lang['category_images_edit'];

            //Отправляем либо фото
            if (isset($ctgr[0]['image'])) {
                sendPhoto($this->bot,$chat_id,$ctgr[0]['image'],$answer,$keyboard);
            }else{
                sendMessage($this->bot,$chat_id,$answer,$keyboard);
            }
        }

        //Показ статистики простмотров
        public function showStats($data,$chat_id,$breads,$keyboard) {
            $stats = new Stats;

            $today_show_films = $stats->getStats("show_film", "today");
            $week_show_films = $stats->getStats("show_film", "week");
            $month_show_films = $stats->getStats("show_film", "lastmonth");

            $today_acc = $stats->getAccountsStats("today");
            $week_acc = $stats->getAccountsStats("week");
            $month_acc = $stats->getAccountsStats("lastmonth");

            $today_time = $stats->getUsedTime("today");
            $week_time = $stats->getUsedTime("week");
            $month_time = $stats->getUsedTime("lastmonth");

            $answer = sprintf($this->lang['statistics_text'],$today_show_films,$week_show_films,$month_show_films,$today_acc,$week_acc,$month_acc,$today_time,$week_time,$month_time);

            $stats = new Stats;
            $stats->addStat($chat_id,"show_stats");

            sendMessage($this->bot,$chat_id,$answer,$keyboard);
        }

        // Добавляем фото к фильму
        public function addCategoryImage($data) {
            if (isset($data['dialog']['data'])) {
                $ctgr_id = $data['dialog']['data'];
                if (isset($data['photo'])) {
                    // Получаем id файла на серверах tg и меняем в фильме
                    $orig_file = $data['photo'][array_key_last($data['photo'])]->getFileId();
                    $this->db->update("UPDATE categories SET image='".$orig_file."' WHERE id='$ctgr_id'");
                    showRpc("edit_category_image", $data['chat_id'], false, false, $ctgr_id);
                }else{
                    // Ошибка если что-то не так
                    $kb = [];
                    array_push($kb, array(array('text'=> $this->lang['back'],'callback_data' => "prew.edit_category_image"),array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kb);
                    sendMessage($this->bot, $data['chat_id'], $this->lang['error_need_photo_ctgr'], $keyboard);
                }
            }
        }
    }