<?php
    // Bot statistics lib

    class Stats extends scriptController{

        public $db_stats;
        public $dbcon_stats;

        //Скриптовые менюшки по которым просчитываем примерное время человека в боте
        //Среднее время проведенное в меню(диалоге, скрипте) в секундах
        public $action_times = [
            'login' => 60, // на и поиск фильма +-60 сек
            'show_film' => 120, // На просмотр карточки фильма +- 120 сек
            'cmd_help' => 30,
            'show_stats' => 60,
        ];

        public function __construct()
        {
            parent::__construct();
            //Подключение к БД статистики
            $this->db_stats = new DB();
            $this->dbcon_stats = $this->db_stats->openNew(STATS_DB_SERVER, STATS_DB_USERNAME, STATS_DB_PASSWORD, STATS_DB_NAME);

            // Удаление статистики по таймауту
            $now_time = new DateTime('now');
            $now_time->modify("-".STAT_TIMEOUT." minutes");
            $this->db->delete("DELETE FROM films WHERE created_at <= '".$now_time->format('Y-m-d H:i:s')."' AND hash IS NULL");
        }

        // Подсчет процентов
        public function calcPercent($num1,$num2){
            if ($num1 == $num2) return 0;
            if ($num2 == 0) return 100;
            if ($num1 == 0) return -100;
            $percent = ($num1/$num2)*100-100;
            return round($percent,1);
        }

        //  Добавляем статистику в Базу данных
        public function addStat($chat_id,$action){
            $this->db_stats->insert("INSERT INTO stats(chat_id,action) VALUES('$chat_id','$action')");
        }

        public function getFilterByPeriod($period) {
            if ($period == "today") { // За сегодня
                $filter = "DATE(`created_at`) = DATE(CURDATE())";
            }elseif ($period == "yesterday") { // За текущий месяц
                $filter = "created_at >= (CURDATE()-1) AND created_at < CURDATE()";
            }elseif ($period == "curmonth") { // За текущий месяц
                $filter = "date_format(created_at, '%Y%m') = date_format(now(), '%Y%m')";
            }elseif ($period == "lastmonth") { // За прошлый месяц
                $filter = "MONTH(created_at) = MONTH(NOW()) - 1";
            }elseif ($period == "week") { // За неделю
                $filter = "`created_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            }elseif ($period == "lastweek") { // За прошлую неделю
                $filter = "WEEKOFYEAR(created_at)=WEEKOFYEAR(CURDATE())-1";
            }

            return $filter;
        }

        // Получаем статистику по действиям
        public function getStats($action,$period = "today") {
            $filter = self::getFilterByPeriod($period);

            $data = $this->db_stats->select("SELECT * FROM stats WHERE action='$action' AND $filter");
            return count($data); // Возвращаем количество записей в статистике
        }

        // Получаем статистику по аккаунтам
        public function getAccountsStats($period = "today") {
            $filter = self::getFilterByPeriod($period);

            $data = $this->db->count("SELECT COUNT(1) FROM _accounts WHERE $filter");
            return $data; // Возвращаем количество записей в статистике
        }

        //Получаем статистику по времени в боте
        public function getUsedTime($period = "today") {
            $filter = self::getFilterByPeriod($period);

            $sec_time = 0;

            $data = $this->db_stats->select("SELECT * FROM stats WHERE $filter");
            foreach ($data as $value) {
                if (isset($this->action_times[$value['action']])) $sec_time+= $this->action_times[$value['action']];
            }

            return round(($sec_time/60),1); // Возвращаем время в минутах
        }

        //Показ статистики простмотров
        public function showStats($data,$chat_id,$breads,$keyboard) {
            // Получаем все данные которые нам надо
            $today_show_films = $this->getStats("show_film", "today");
            $week_show_films = $this->getStats("show_film", "week");
            $month_show_films = $this->getStats("show_film", "curmonth");
            $yesterday_show_films = $this->getStats("show_film", "yesterday");
            $lastweek_show_films = $this->getStats("show_film", "lastweek");
            $lastmonth_show_films = $this->getStats("show_film", "lastmonth");

            $today_show_percent = $this->calcPercent($today_show_films,$yesterday_show_films); // Подсчет процентов (сегодня и вчера)
            $week_show_percent = $this->calcPercent($week_show_films,$lastweek_show_films);
            $month_show_percent = $this->calcPercent($month_show_films,$lastmonth_show_films);

            // По аккаунтам
            $today_acc = $this->getAccountsStats("today");
            $week_acc = $this->getAccountsStats("week");
            $month_acc = $this->getAccountsStats("curmonth");
            $y_acc = $this->getAccountsStats("yesterday");
            $lw_acc = $this->getAccountsStats("lastweek");
            $lm_acc = $this->getAccountsStats("lastmonth");

            $today_acc_percent = $this->calcPercent($today_acc,$y_acc); // Подсчет процентов (сегодня и вчера)
            $week_acc_percent = $this->calcPercent($lw_acc,$week_acc);
            $month_acc_percent = $this->calcPercent($lm_acc,$month_acc);

            // Затраченное время
            $today_time = $this->getUsedTime("today");
            $week_time = $this->getUsedTime("week");
            $month_time = $this->getUsedTime("curmonth");
            $y_time = $this->getUsedTime("yesterday");
            $lw_time = $this->getUsedTime("lastweek");
            $lm_time = $this->getUsedTime("lastmonth");

            $today_time_percent = $this->calcPercent($today_time,$y_time); // Подсчет процентов (сегодня и вчера)
            $week_time_percent = $this->calcPercent($week_time,$lw_time);
            $month_time_percent = $this->calcPercent($month_time,$lm_time);

            $answer = sprintf($this->lang['statistics_text'],$today_show_films,formatPercent($today_show_percent),$week_show_films,formatPercent($week_show_percent),$month_show_films,formatPercent($month_show_percent),$today_acc,formatPercent($today_acc_percent),$week_acc,formatPercent($week_acc_percent),$month_acc,formatPercent($month_acc_percent),$today_time,formatPercent($today_time_percent),$week_time,formatPercent($week_time_percent),$month_time,formatPercent($month_time_percent));

            $this->addStat($chat_id,"show_stats");

            sendMessage($this->bot,$chat_id,$answer,$keyboard);
        }
    }