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

		public $msg_id;

		//UserData
		public $chat_id;
		public $username;
		public $lastname;
		public $firstname;
		public $user_data;
		public $rpc;

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
            $this->rpc = new Rpc();

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

            //Обработка
            self::controller();

            try {
                $this->bot->answerCallbackQuery($Callback->getId());
            }catch (TelegramBot\Api\HttpException $e) {
                exit();
            }
            exit();
		}

		public function controller()
        {
            $query = $this->Callback->getData();
            $data = explode(".", $query);
            $dialog = $this->db->select("SELECT * FROM dialogs WHERE chat_id='".$this->chat_id."' ORDER BY created_at DESC LIMIT 1");

            // Обработка каллбеков для перекидывания сразу на меню ( с сохраненим параметров id)
            if ($data[0] == "view" && isset($data[1])) {
                $data2 = false;
                if (isset($dialog[0]['data'])) $data2 = $dialog[0]['data'];
                $this->rpc->show($data[1],$this->chat_id,false, $this->msg_id,$data2);
            }// Обработка каллбеков для перекидывания сразу на меню ( с сохраненим параметров id)
            if ($data[0] == "catalog_page" && isset($data[1])) {
                $data2 = false;
                if (isset($dialog[0]['data'])) $data2 = $dialog[0]['data'];
                $this->rpc->show($dialog[0]['menu'],$this->chat_id,false, $this->msg_id,$data2,$data[1]);
            }
            // Обработка хешей на меню ( с сохраненим параметров id)
            if ($data[0] == "hash" && isset($data[1])) {
                $hash = $this->db->select("SELECT * FROM dialogs WHERE id='".$data[1]."'");
                if (isset($hash[0]['id'])) {
                    $data3 = false;
                    if (isset($dialog[0]['data'])) $data3 = $hash[0]['data'];
                    $this->rpc->show($hash[0]['menu'],$this->chat_id,false, $this->msg_id,$data3);
                }
            }
            // предыдущая страница
            if ($data[0] == "prew" && isset($data[1])) {
                $dialog = $this->db->select("SELECT * FROM dialogs WHERE chat_id='".$this->chat_id."' ORDER BY created_at DESC LIMIT 2");
                $data2 = false;
                if (isset($dialog[0]['data'])) $data2 = $dialog[0]['data'];
                foreach ($dialog as $diag) {
                    $this->db->delete("DELETE FROM dialogs WHERE id='".$diag['id']."'");
                }
                $this->rpc->show($data[1],$this->chat_id,false, $this->msg_id,$data2);
            }


        }
	}