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
        public function createMediaGroup($film,$card = false) {
            $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
            if (isset($film[0]['photo'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto($film[0]['photo'],$film[0]['text'],"html"));
            }
            if (isset($film[0]['trailer'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['trailer']));
            }
            if (isset($film[0]['video']) && !$card) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['video']));
            }

            return $media;
        }

        //Сортировка по ключу в массиве
        public function compareByName($a, $b) {
            return strcmp($a["name"], $b["name"]);
        }

        //Получение кнопок с пагинацией
        public function getButtons($page,$content) {
            // Пагинатор
            $per_page = PER_PAGE;
            $per_page_col = PER_PAGE_COL;
            $count = count($content);
            $total = intval(($count - 1) / $per_page) + 1;
            if(empty($page) or $page < 0) $page = 1;
            if($page > $total) $page = $total;
            $start = $page * $per_page - $per_page;
            // сортировка
            usort($content, array($this, "compareByName"));
            $content = array_slice($content,$start,$per_page);
            $main_array = [];

            foreach ($content as $cont) {
                array_push($main_array, array('text'=>$cont['name'],'callback_data' => "select.".$cont['id']));
            }

            // Разбиваем на колонки
            $main_array = array_chunk($main_array,$per_page_col);

            if ($count > PER_PAGE) {
                $paginator = [];
                if ($page != 1) array_push($paginator, array('text'=>$this->lang['prew'],'callback_data' => 'catalog_page.'.($page - 1)));
                array_push($paginator, array('text'=>$page."/".$total,'callback_data' => '1'));
                if ($page != $total) array_push($paginator, array('text'=>$this->lang['next'],'callback_data' => 'catalog_page.'.($page + 1)));
                array_push($main_array, $paginator);
            }

            return $main_array;
        }

	}