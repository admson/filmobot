<?php

    function menuCatalog($page = 1) {
        global $db;

        $per_page = PER_PAGE;
        $count = $db->count("SELECT COUNT(1) FROM categories");

        $total = intval(($count - 1) / $per_page) + 1;

        if(empty($page) or $page < 0) $page = 1;
        if($page > $total) $page = $total;

        $start = $page * $per_page - $per_page;

        $categories = $db->select("SELECT * FROM categories LIMIT $start,$per_page");
        $main_array = [];

        foreach ($categories as $ctgr) {
            array_push($main_array, array(array('text'=>$ctgr['name'],'callback_data' => 'vieww.set_category.'.$ctgr['id'])));
        }

        if ($count > 3) {
            $paginator = [];
            if ($page != 1) array_push($paginator, array('text'=>"⬅",'callback_data' => 'catalog_page.'.($page - 1)));
            array_push($paginator, array('text'=>"Стр. ".$page,'callback_data' => '1'));
            if ($page != $total) array_push($paginator, array('text'=>"➡️",'callback_data' => 'catalog_page.'.($page + 1)));
            array_push($main_array, $paginator);
        }

        return $main_array;
    }