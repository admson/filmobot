<?php
    class Main extends scriptController
    {
        // Берем данные с scriptController
        // $this->bot - бот, $this->db - база данных, $this->lang - язык
        public function __construct()
        {
            parent::__construct();

            // Диалоги сюжета
            $this->routes = [
                "main" => [
                    'name' => "список категорий",
                    'answer' => $this->lang['choose_categories'],
                    'keyboard_func' => "getCategories",
                    'callback_menu' => "films", // Меню после нажатия кнопки (id)
                    'clean_cache' => true,
                    'prev_menu' => false,
                ],

                "films" => [
                    'name' => "список фильмов",
                    'answer' => $this->lang['choose_film'],
                    'keyboard_func' => "getFilms",
                    'callback_menu' => "film", // getFilms перекинет на film
                    'prev_menu' => "main",
                ],

                "film" => [
                    'name' => "",
                    'view_func' => 'showFilm', // Функция вывода фильма
                    'prev_menu' => "films",
                    'reactions' => true
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
                    'answer' => $this->lang['statistics_text'],
                    'prev_menu' => false,
                    'inline_keyboard' => [
                        array(array('text'=> $this->lang['stats_btn_shows'],'callback_data' => "view.stats_shows")),
                        array(array('text'=> $this->lang['stats_btn_users'],'callback_data' => "view.stats_users")),
                        array(array('text'=> $this->lang['stats_btn_showtime'],'callback_data' => "view.stats_time")),
                    ],
                ],

                "stats_shows" => [
                    'name' => "статистика показов",
                    'view_func' => 'statsShows', // статистика
                    'prev_menu' => "statistics",
                ],
            ];
        }

        // Обязательная функция в скриптах
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
            // todo
        }

        //Показ категорий к фильмам
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
            $media = filmoBot::createMediaGroup($film,true);
            sendMediaGroup($this->bot,$chat_id,$media);

            //Добавляем +1 просмотр в статистику
            $stats = new Stats;
            $stats->addStat($chat_id,"show_film");

            //Хлебные крошки
            $answer = $breads.mb_strtolower("<i><a href='https://t.me/".$bot_username."?start=".$film[0]['hash']."'>".$film[0]['name']."</a></i>")."\n\n".$film[0]['name']." (".$film[0]['year'].")";

            //Отправляем ролик с кнопками
            sendVideo($this->bot,$chat_id,$film[0]['video'],$answer,$keyboard);
        }

        //Показ статистики простмотров
        public function statsShows() {

        }
    }
