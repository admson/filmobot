<?php

	class viewController
	{
		//Main data
        private $db;
        private $dbconnection;
        public $bot;
		public $msg_id;
		public $lang;

		//UserData
		public $chat_id;
		public $username;
		public $lastname;
		public $firstname;
		public $user_data;

		//admins
        public $admins;

        // breadcrumbs
        public $breadcrumbs = [
                "main" => "menuMain",
                "admin" => "menuAdmin",
                "quiz" => "menuQuiz",
                "quiz_end" => "menuQuizEnd",
                "quiz2" => "menuQuiz2",
                "quiz3" => "menuQuiz3",
            ];

		function __construct($bot,$user_data,$msg_id = false, $db, $dbconnection, $lang)
		{
		    global $admins;
		    $this->admins = $admins;
			$this->bot = $bot;
			$this->msg_id = $msg_id;
            $this->db = $db;
            $this->dbconnection = $dbconnection;
            $this->lang = $lang;

            // set user_data
            $this->firstname = $user_data[0]['first_name'];
            $this->lastname = $user_data[0]['last_name'];
            $this->username = $user_data[0]['username'];
            $this->chat_id = $user_data[0]['chat_id'];
            $this->user_data = $user_data;
		}

		public function updateUserData($data) {
            $this->user_data = $data;
        }

        public function getBreadCrumps($menu) {
            $bot_username = $this->bot->getMe()->getUsername();
            $breads = explode("/",$menu);
            $output = "";
            foreach ($breads as $bread) {
                $hash = md5($bread);
                $output.= "/<a href='https://t.me/".$bot_username."?start=".$hash."'>".$bread."</a>";
            }
            return $output;
        }

        public function updateState($menu,$data = false) {
		    $state_hash = md5($menu);
		    $new_state = $this->db->update("INSERT INTO dialogs(hash,chat_id,menu) VALUES('$state_hash','".$this->chat_id."','$menu')");
        }

        // View главного меню
        public function menuMain() {
		    $menu = "main";
            self::updateState($menu);
            $main_keyboard = [];
            array_push($main_keyboard, array(array('text'=>$this->lang['startmenu'])));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='$menu' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang['welcome_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }


        // view под админку
        public function menuAdmin() {
            $menu = "admin";
            self::updateState($menu);
            $main_keyboard = [];
            array_push($main_keyboard, array(array('text'=>$this->lang['add_film']),array('text'=>$this->lang['del_film'])));
            array_push($main_keyboard, array(array('text'=>$this->lang['test'])));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='$menu' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang['admin_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz() {
            $menu = "admin/quiz";
            self::updateState($menu);
            $main_array = [];

            array_push($main_array, array(array('text'=> "Ответ 1",'callback_data' => "set_answer1.1")));
            array_push($main_array, array(array('text'=> "Ответ 2",'callback_data' => "set_answer1.2")));
            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['cancel'],'callback_data' => "mainadmin")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='quiz' WHERE chat_id='$this->chat_id'");
            $breadcrumps = self::getBreadCrumps($menu);
            $answer = "<i>$breadcrumps</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuizEnd() {
            $menu = "admin/quiz/quiz2/quiz_end";
            self::updateState($menu);
            $main_array = [];

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz2")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='$menu' WHERE chat_id='$this->chat_id'");
            $breadcrumps = self::getBreadCrumps($menu);
            $answer1 = $this->db->select("SELECT * FROM quiz WHERE chat_id='".$this->chat_id."'");
            $answer = "<i>$breadcrumps</i>\n\nВаш ответ на первый вопрос <b>".$answer1[0]['answer1']."</b> на второй вопрос <b>".$answer1[0]['answer2']."</b>";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz2() {
            $menu = "admin/quiz/quiz2";
            self::updateState($menu);
            $main_array = [];

            array_push($main_array, array(array('text'=> "Ответ 3",'callback_data' => "set_answer2.3")));
            array_push($main_array, array(array('text'=> "Ответ 4",'callback_data' => "set_answer2.4")));

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='$menu' WHERE chat_id='$this->chat_id'");
            $breadcrumps = self::getBreadCrumps($menu);
            $answer1 = $this->db->select("SELECT * FROM quiz WHERE chat_id='".$this->chat_id."'");
            $answer = "<i>$breadcrumps</i>\n\nВы выбрали ответ номер <b>".$answer1[0]['answer1']."</b>\n\nВыберите ответ на второй вопрос";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz3() {
            $menu = "admin/quiz/quiz3";
            self::updateState($menu);
            $main_array = [];

            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='$menu' WHERE chat_id='$this->chat_id'");
            $breadcrumps = self::getBreadCrumps($menu);
            $answer = "<i>$breadcrumps</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

	}

