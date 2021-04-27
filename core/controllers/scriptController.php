<?php
	// Абстракный класс под скрипты
	abstract class scriptController {

	    // Данные которые будут доступны в функциях
        public $db;
        public $dbconnection;
        public $bot;
        public $lang;
        public $routes;

        // Конструктор
        public function __construct(){
            global $db,$dbconnection,$bot,$lang,$routes;

            $this->db = $db;
            $this->dbconnection = $dbconnection;
            $this->bot = $bot;
            $this->lang = $lang;
            $this->routes = $routes;
        }

        // Функция получения хеш-тегов
        public function getHashtags($string) {
            $hashtags= FALSE;
            preg_match_all("/(#\w+)/u", $string, $matches);
            if ($matches) {
                $hashtagsArray = array_count_values($matches[0]);
                $hashtags = array_keys($hashtagsArray);
            }
            return $hashtags;
        }

        // Создание медиагруппы
        public function createMediaGroup($film) {
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

            return $media;
        }

        //Получение кнопок с пагинацией
        public function getButtons($page,$content) {
            // Пагинатор
            $per_page = PER_PAGE;
            $count = count($content);
            $total = intval(($count - 1) / $per_page) + 1;
            if(empty($page) or $page < 0) $page = 1;
            if($page > $total) $page = $total;
            $start = $page * $per_page - $per_page;

            $content = array_slice($content,$start,$per_page);
            $main_array = [];

            foreach ($content as $cont) {
                array_push($main_array, array(array('text'=>$cont['name'],'callback_data' => "select.".$cont['id'])));
            }

            if ($count > PER_PAGE) {
                $paginator = [];
                if ($page != 1) array_push($paginator, array('text'=>$this->lang['prew'],'callback_data' => 'catalog_page.'.($page - 1)));
                array_push($paginator, array('text'=>$this->lang['curpage'].$page,'callback_data' => '1'));
                if ($page != $total) array_push($paginator, array('text'=>$this->lang['next'],'callback_data' => 'catalog_page.'.($page + 1)));
                array_push($main_array, $paginator);
            }

            return $main_array;
        }

	}