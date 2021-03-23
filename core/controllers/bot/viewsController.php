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

            if (in_array($this->chat_id,$this->admins)) {
                array_push($main_keyboard, array(array('text'=>$this->lang['adminmenu'])));
            }

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
            array_push($main_keyboard, array(array('text'=>$this->lang['add_card_film'])));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='main' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang['admin_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }
        
	}

