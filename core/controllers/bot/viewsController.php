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

        // View главного меню
        public function menuMain() {
            
            $main_keyboard = [];
            array_push($main_keyboard, array(array('text'=>$this->lang['startmenu'])));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='main' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang['welcome_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }


        // view под админку
        public function menuAdmin() {
            $main_keyboard = [];
            array_push($main_keyboard, array(array('text'=>$this->lang['add_film']),array('text'=>$this->lang['del_film'])));
            array_push($main_keyboard, array(array('text'=>$this->lang['test'])));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='admin' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang['admin_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz() {
            $main_array = [];

            array_push($main_array, array(array('text'=> "Ответ 1",'callback_data' => "view.menuQuiz1")));
            array_push($main_array, array(array('text'=> "Ответ 2",'callback_data' => "view.menuQuiz2")));
            array_push($main_array, array(array('text'=> "Ответ 3",'callback_data' => "view.menuQuiz3")));
            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['cancel'],'callback_data' => "mainadmin")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='quiz' WHERE chat_id='$this->chat_id'");
            $answer = "<i>/admin/quiz</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz1() {
            $main_array = [];

            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='quiz1' WHERE chat_id='$this->chat_id'");
            $answer = "<i>/admin/quiz/quiz1</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz2() {
            $main_array = [];

            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='quiz2' WHERE chat_id='$this->chat_id'");
            $answer = "<i>/admin/quiz/quiz2</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuQuiz3() {
            $main_array = [];

            array_push($main_array, array(array('text'=> "URL",'url' => "https://t.me/")));

            array_push($main_array, array(array('text'=> $this->lang['back'],'callback_data' => "view.menuQuiz")));

            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='quiz3' WHERE chat_id='$this->chat_id'");
            $answer = "<i>/admin/quiz/quiz3</i>\n\nВыберите ответ";

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }
        
	}

