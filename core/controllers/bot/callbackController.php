<?php
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

            $this->view = new viewController($this->bot, $this->user_data, false, $db, $dbconnection, $lang);

            $this->vieww = new viewController($this->bot, $this->user_data, $this->msg_id, $db, $dbconnection, $lang);

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
            $lng = getLang($this->user_data[0]['lang']);
            $query = $this->Callback->getData();
            $data = explode(".", $query);

            if ($data[0] == "view" && isset($data[1])) {
                call_user_func(array($this->vieww, $data[1]));
            }
            if ($data[0] == "vieww" && isset($data[1]) && isset($data[2])) {
                call_user_func(array($this->vieww, $data[1]), $data[2]);
            }
            if ($data[0] == "mainadmin") {
                if (isset($this->msg_id)) $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                $this->view->menuAdmin();
            }

        }
	}