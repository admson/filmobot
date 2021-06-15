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