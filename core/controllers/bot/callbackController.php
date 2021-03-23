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


            if ($data[0] == "remove_media" && $data[1] >= 1) {
                $mail = $this->db->select("SELECT * FROM mail WHERE id='" . $data[1] . "'");
                unlink("./bots/" . $this->chat_id . "/" . $mail[0]['media']);
                $this->db->update("UPDATE mail SET media=NULL WHERE author_id='" . $this->chat_id . "'");

                $view = $this->vieww->menuEditMail($data[1]);
            }
            if ($data[0] == "del_caption" && $data[1] >= 1) {
                $this->db->update("UPDATE mail SET caption=NULL WHERE id='" . $data[1] . "'");

                $view = $this->vieww->menuEditMail($data[1]);
            }
            if ($data[0] == "del_url_buttons" && $data[1] >= 1) {
                $this->db->update("UPDATE mail SET url_buttons=NULL WHERE author_id='" . $this->chat_id . "'");

                $view = $this->vieww->menuEditMail($data[1]);
            }

            if ($data[0] == "set_lang") {
                $this->db->update("UPDATE accounts SET lang='".$data[1]."' WHERE chat_id='" . $this->chat_id . "'");
                $user = new authController($this->chat_id, $this->username, $this->firstname, $this->lastname);
                $this->user_data = $user->authUser();
                $this->vieww->updateUserData($this->user_data);
                if (!is_null($this->user_data[0]['contact'])) {
                    $this->vieww->menuMain(); // Показываем главное меню
                }else{
                    $this->vieww->sendContact(); // Меню отправки контакта (номера телефона)
                }
            }

            if ($data[0] == "ready_mail_all" && $data[1] >= 1) {
                $mail = $this->dbb->select("SELECT * FROM mail WHERE id='".$data[1]."'");
                $this->dbb->update("UPDATE mail SET user_type=1 WHERE id='".$data[1]."'");
                $this->dbb->update("UPDATE mail SET status=1 WHERE id='".$data[1]."'");
                $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                sendMessage($this->bot,$this->chat_id, "✅ Рассылка началась, вы получите уведомление когда она закончиться.");
                $accounts = $this->dbb->select("SELECT * FROM accounts WHERE banned != 1 ORDER BY last_action DESC");
                $sended = 0;
                $banneds = 0;
                foreach ($accounts as $acc) {
                    $banned = sendMail($this->bot,$acc['chat_id'],$mail);
                    if ($banned == "banned") {
                        $this->dbb->update("UPDATE accounts SET banned=1 WHERE id='".$acc['id']."'");
                        $banneds++;
                    }else{
                        if ($acc['banned'] == 1) $this->dbb->update("UPDATE accounts SET banned=0 WHERE id='".$acc['id']."'");
                        $sended++;
                    }
                }
                $this->dbb->update("UPDATE mail SET status=2 WHERE id='".$data[1]."'");
                sendMessage($this->bot,$this->chat_id, "✅ Рассылка закончилась.\nОтправлено сообщений <b>$sended шт.</b> \nЗаблокировали бота <b>$banneds шт.</b>");
            }

        }
	}