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
            ];
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
            $media = parent::createMediaGroup($film,true);
            sendMediaGroup($this->bot,$chat_id,$media);

            //Хлебные крошки
            $answer = $breads.mb_strtolower("<i>".$film[0]['name']."</i>")."\n\n".$film[0]['name']." (".$film[0]['year'].")";

            //Отправляем ролик с кнопками
            sendVideo($this->bot,$chat_id,$film[0]['video'],$answer,$keyboard);
        }
    }
