<?php
    //include "core/admin/callbacks.php";

	class callbackController
	{
		//Main data
        private $db;
        private $dbconnection;
		public $bot;
		public $Callback;
		public $Message;
		public $lang;
		public $view;
		public $vieww;
		public $msg_id;

		//UserData
		public $chat_id;
		public $username;
		public $lastname;
		public $firstname;
		public $user_data;

		function __construct($bot,$db,$dbconnection,$Callback,$lang)
		{
			$this->bot = $bot;
            $this->db = $db;
            $this->dbconnection = $dbconnection;
			$this->Callback = $Callback;
			$this->Message = $Callback->getMessage();
			$this->lang = $lang;

            // set user_data
            $this->firstname = $this->Message->getChat()->getFirstName();
            $this->lastname = $this->Message->getChat()->getLastName();
            $this->username = $this->Message->getChat()->getUsername();
            $this->chat_id = $this->Message->getChat()->getId();
            $this->lng = $this->Message->getFrom()->getLanguageCode();
            $this->msg_id = $this->Message->getMessageId();

            $user = new authController($this->chat_id, $this->username, $this->firstname, $this->lastname);
            $this->user_data = $user->authUser();
            if ($this->user_data[0]['status'] == 2) {
                try {
                    $this->bot->answerCallbackQuery($Callback->getId());
                    exit();
                }catch (TelegramBot\Api\HttpException $e) {
                    exit();
                }
            }

            self::controller();

            try {
                $this->bot->answerCallbackQuery($Callback->getId());
            }catch (TelegramBot\Api\HttpException $e) {
                $view = $this->vieww->menuMain();
                exit();
            }
            exit();
		}

		public function controller()
        {
            $query = $this->Callback->getData();
            $data = explode(".", $query);

            if ($data[0] == "view" && isset($data[1])) {
                showRcp($data[1],$this->chat_id,false, $this->msg_id);
            }
            if ($data[0] == "hash" && isset($data[1])) {
                $hash = $this->db->select("SELECT * FROM dialogs WHERE id='".$data[1]."'");
                if (isset($hash[0]['id'])) {
                    showRcp($hash[0]['menu'],$this->chat_id,false, $this->msg_id);
                }
            }


        }
	}