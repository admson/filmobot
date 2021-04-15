<?php
    $newroutes = [
        "main" => [
            'name' => "главная",
            'answer' => $lang['choose_categories'],
            'keyboard_func' => "menuMain",
            'clean_cache' => true,
            'prev_menu' => false,
        ],
    ];

    class Main extends scriptController
    {
        // Берем данные с scriptController
        // $this->bot - бот, $this->db - база данных, $this->lang - язык
        public function __construct()
        {
            parent::__construct();
        }

    }
