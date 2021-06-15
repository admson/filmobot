<?php
    // Bot statistics lib

    class Stats extends scriptController{

        public function __construct()
        {
            parent::__construct();
        }

        //  Добавляем статистику в Базу данных
        public function addStat($chat_id,$action){
            $this->db->insert("INSERT INTO stats(chat_id,action) VALUES('$chat_id','$action')");
        }

        // Получаем статистику по действиям
        public function getStats($action,$period = "today") {
            if ($period == "today") { // За сегодня
                $filter = "DATE(`created_at`) = DATE(CURDATE())";
            }elseif ($period == "curmonth") { // За текущий месяц
                $filter = "date_format(created_at, '%Y%m') = date_format(now(), '%Y%m')";
            }elseif ($period == "lastmonth") { // За прошлый месяц
                $filter = "MONTH(created_at) = MONTH(NOW()) - 1";
            }elseif ($period == "week") { // За неделю
                $filter = "`created_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            }

            $data = $this->db->select("SELECT * FROM stats WHERE action='$action' AND $filter");
            if (!empty($data)) {
                return count($data); // Возвращаем количество записей в статистике
            }else{
                return false;
            }
        }

        //
    }