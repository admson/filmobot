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
            $lng = getLang($this->user_data[0]['lang']);

            array_push($main_keyboard, array(array('text'=>$this->lang[$lng]['startmenu'])));

            if (in_array($this->chat_id,$this->admins)) {
                array_push($main_keyboard, array(array('text'=>$this->lang[$lng]['adminmenu'])));
            }

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='main' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang[$lng]['welcome_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }


        // view под админку
        public function menuAdmin() {

            $lng = getLang($this->user_data[0]['lang']);

            $main_keyboard = [];
            array_push($main_keyboard, array(array('text'=>$this->lang[$lng]['add_film']),array('text'=>$this->lang[$lng]['del_film'])));
            array_push($main_keyboard, array(array('text'=>$this->lang[$lng]['add_card_film'])));
            array_push($main_keyboard, array(array('text'=>"✉️ Рассылка в боте")));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='main' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang[$lng]['admin_answer'];

            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function sendContact() {

            $main_keyboard = [];

            $lng = getLang($this->user_data[0]['lang']);

            array_push($main_keyboard, array(array('text'=>$this->lang[$lng]['send_contact'], 'request_contact' => true)));

            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($main_keyboard,false, true);
            $this->db->update("UPDATE accounts SET menu='send_contact' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang[$lng]['send_contact_answer'];

            sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
        }

        public function setLang($lng) {

            $main_array = [];
            array_push($main_array, array(array('text'=>"🇷🇺 Русский",'callback_data' => 'set_lang.ru'),array('text'=>"🇺🇿 Uzbekistan",'callback_data' => 'set_lang.en')));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='set_lang' WHERE chat_id='$this->chat_id'");
            $answer = $this->lang[$lng]['set_lang_answer'];

            sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
        }
        

        public function menuMail() {
            
            $main_array = [];
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='addmail' WHERE chat_id='$this->chat_id'");
            $answer = "Отправьте боту то, что хотите отправить. Это может быть текст, картинка, видео, гифка или стикер.";
            if (isset($this->msg_id) && $this->msg_id > 0) {
                editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$this->chat_id,$answer,$keyboard );
            }
        }

        public function menuEditMail($id = false) {
            
            if (isset($id) && $id >= 1) {
                $mail = $this->db->select("SELECT * FROM mail WHERE id='$id'");
            }else{
                $mail = $this->db->select("SELECT * FROM mail WHERE author_id='".$this->chat_id."' AND status=0");
            }
            if (isset($mail[0]['id'])) {
                $main_array = [];
                //buttons
                if (isset($mail[0]['url_buttons'])) {
                    $url_buttons = json_decode($mail[0]['url_buttons'],true);
                    foreach ($url_buttons as $button) {
                        array_push($main_array,$button);
                    }
                }
                // main buttons
                $caption = null;
                if (isset($mail[0]['caption'])) $caption = $mail[0]['caption'];
                $this->db->update("UPDATE accounts SET menu='edit_mail' WHERE chat_id='$this->chat_id'");
                if (isset($mail[0]['text']) && !is_null($mail[0]['text'])) {
                    array_push($main_array, array(array('text'=>"Изменить текст",'callback_data' => 'vieww.editMailText.'.$mail[0]['id'])));
                    if (isset($mail[0]['media'])) {
                        array_push($main_array, array(array('text'=>"Удалить превью",'callback_data' => 'remove_media.'.$mail[0]['id'])));
                        $file_url = "https://admaker.tech/bot/bots/".$this->chat_id."/".$mail[0]['media'];
                        $mail[0]['text'].= "<a href='$file_url'>&#8205;</a>";
                    }else{
                        array_push($main_array, array(array('text'=>"Прикрепить превью",'callback_data' => 'vieww.addMedia.'.$mail[0]['id'])));
                    }
                    if (isset($mail[0]['url_buttons'])) {
                        array_push($main_array, array(array('text'=>"Удалить URL Кнопки",'callback_data' => 'del_url_buttons.'.$mail[0]['id'])));
                    }
                    array_push($main_array, array(array('text'=>"URL Кнопки",'callback_data' => 'vieww.addUrlButtons.'.$mail[0]['id'])));
                    array_push($main_array, array(array('text'=>"✔ Готово!",'callback_data' => 'view.menuReadyToMail')));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);

                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        editMessage($this->bot,$this->chat_id,$this->msg_id,$mail[0]['text'],$keyboard);
                    }else{
                        sendMessage($this->bot,$this->chat_id,$mail[0]['text'],$keyboard );
                    }
                }
                //
                if (isset($mail[0]['photo']) || isset($mail[0]['video']) || isset($mail[0]['gif'])) {
                    if (isset($mail[0]['caption'])) {
                        array_push($main_array, array(array('text'=>"Изменить описание",'callback_data' => 'vieww.editPostCaption.'.$mail[0]['id'])));
                        array_push($main_array, array(array('text'=>"Удалить описание",'callback_data' => 'del_caption.'.$mail[0]['id'])));
                    }else{
                        array_push($main_array, array(array('text'=>"Добавить описание",'callback_data' => 'vieww.editPostCaption.'.$mail[0]['id'])));
                    }
                    if (isset($mail[0]['url_buttons'])) {
                        array_push($main_array, array(array('text'=>"Удалить URL Кнопки",'callback_data' => 'del_url_buttons.'.$mail[0]['id'])));
                    }
                    array_push($main_array, array(array('text'=>"URL Кнопки",'callback_data' => 'vieww.addUrlButtons.'.$mail[0]['id'])));
                    array_push($main_array, array(array('text'=>"✔ Готово!",'callback_data' => 'view.menuReadyToMail')));
                }
                if (isset($mail[0]['photo']) && !is_null($mail[0]['photo'])) {
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);

                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                        sendPhoto($this->bot,$this->chat_id,$mail[0]['photo'],$caption,$keyboard);
                    }else{
                        sendPhoto($this->bot,$this->chat_id,$mail[0]['photo'],$caption,$keyboard);
                    }
                }

                if (isset($mail[0]['video']) && !is_null($mail[0]['video'])) {
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                        sendVideo($this->bot,$this->chat_id,$mail[0]['video'],$caption,$keyboard);
                    }else{
                        sendVideo($this->bot,$this->chat_id,$mail[0]['video'],$caption,$keyboard);
                    }
                }

                if (isset($mail[0]['gif']) && !is_null($mail[0]['gif'])) {
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                        sendAnimation($this->bot,$this->chat_id,$mail[0]['gif'],$caption,$keyboard);
                    }else{
                        sendAnimation($this->bot,$this->chat_id,$mail[0]['gif'],$caption,$keyboard);
                    }
                }

                if (isset($mail[0]['sticker']) && !is_null($mail[0]['sticker'])) {
                    array_push($main_array, array(array('text'=>"✔ Готово!",'callback_data' => 'view.menuReadyToMail')));
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                        sendSticker($this->bot,$this->chat_id,$mail[0]['sticker'],$keyboard);
                    }else{
                        sendSticker($this->bot,$this->chat_id,$mail[0]['sticker'],$keyboard);
                    }
                }
            }
        }

        public function editMailText($post_id = false) {
            
            if (isset($id) && $id >= 1) {
                $mail = $this->db->select("SELECT * FROM mail WHERE id='$id'");
            }else{
                $mail = $this->db->select("SELECT * FROM mail WHERE author_id='".$this->chat_id."' AND status=0");
            }

            $this->db->update("UPDATE accounts SET menu='edit_mail_text' WHERE chat_id='$this->chat_id'");

            $main_array = [];
            array_push($main_array, array(array('text'=>"🔙 Назад",'callback_data' => 'vieww.menuEditMail.'.$mail[0]['id'])));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $answer = "Отправльте боту новый текст:";
            editMessage($this->bot,$this->chat_id,$this->msg_id,$answer,$keyboard);
        }

        public function editPostCaption($post_id = false) {
            
            if (isset($id) && $id >= 1) {
                $mail = $this->db->select("SELECT * FROM mail WHERE id='$id'");
            }else{
                $mail = $this->db->select("SELECT * FROM mail WHERE author_id='".$this->chat_id."' AND status=0");
            }

            $this->db->update("UPDATE accounts SET menu='edit_mail_caption' WHERE chat_id='$this->chat_id'");

            $main_array = [];
            array_push($main_array, array(array('text'=>"🔙 Назад",'callback_data' => 'vieww.menuEditMail.'.$mail[0]['id'])));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $answer = "Отправльте боту новое описание:";
            if ($this->msg_id) $this->bot->deleteMessage($this->chat_id,$this->msg_id);
            sendMessage($this->bot,$this->chat_id,$answer,$keyboard);
        }

        public function addUrlButtons($post_id = false) {
            
            if (isset($post_id) && $post_id >= 1) {
                $post = $this->db->select("SELECT * FROM mail WHERE id='$post_id'");
            }else{
                $post = $this->db->select("SELECT * FROM mail WHERE author_id='".$this->chat_id."'");
            }

            $this->db->update("UPDATE accounts SET menu='add_url_buttons' WHERE chat_id='$this->chat_id'");

            $main_array = [];
            array_push($main_array, array(array('text'=>"🔙 Назад",'callback_data' => 'vieww.menuEditMail.'.$post[0]['id'])));
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $answer = "Отправьте боту список кнопок в таком формате:\n<pre>Кнопка 1 - http://example1.com\nКнопка 2 - http://example2.com</pre>\n\nИспользуйте разделитель '|', чтобы добавить до трех кнопок в один ряд:\n<pre>Кнопка 1 - http://example1.com | Кнопка 2 - http://example2.com\nКнопка 3 - http://example3.com | Кнопка 4 - http://example4.com</pre><a href='https://admaker.tech/botimg/url_button.png'>&#8205;</a>";
            if ($this->msg_id) $this->bot->deleteMessage($this->chat_id,$this->msg_id);
            sendMessage($this->bot,$this->chat_id,$answer,$keyboard);
        }

        public function menuReadyToMail($id = false) {
            
            if (isset($id) && $id >= 1) {
                $mail = $this->db->select("SELECT * FROM mail WHERE id='$id'");
            }else{
                $mail = $this->db->select("SELECT * FROM mail WHERE author_id='".$this->chat_id."' AND status=0");
            }
            if (isset($mail[0]['id'])) {
                $main_array = [];
                array_push($main_array, array(array('text'=>"🔹 Отправить всем",'callback_data' => 'ready_mail_all.'.$mail[0]['id'])));
                array_push($main_array, array(array('text'=>"🔙 Назад",'callback_data' => 'vieww.menuEditMail.'.$mail[0]['id'])));
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
                $caption = null;
                $additional = "\n\n<b>Выбери кому хочешь сделать рассылку</b> 👇";
                if (isset($mail[0]['caption'])) $caption = $mail[0]['caption'].$additional;

                if (isset($mail[0]['text']) && !is_null($mail[0]['text'])) {
                    if (isset($mail[0]['media'])) {
                        $file_url = "https://admaker.tech/bot/bots/".$this->chat_id."/".$mail[0]['media'];
                        $mail[0]['text'].= "<a href='$file_url'>&#8205;</a>";
                    }
                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        editMessage($this->bot,$this->chat_id,$this->msg_id,$mail[0]['text'].$additional,$keyboard);
                    }else{
                        sendMessage($this->bot,$this->chat_id,$mail[0]['text'].$additional,$keyboard );
                    }
                }
                if (isset($mail[0]['photo']) && !is_null($mail[0]['photo'])) {
                    if (isset($this->msg_id) && $this->msg_id > 0) {
                        $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                        sendPhoto($this->bot,$this->chat_id,$mail[0]['photo'],$caption,$keyboard);
                    }else{
                        sendPhoto($this->bot,$this->chat_id,$mail[0]['photo'],$caption,$keyboard);
                    }
                }
                if (isset($mail[0]['video']) && !is_null($mail[0]['video'])) {
                    $caption = null;
                    sendVideo($this->bot,$this->chat_id,$mail[0]['video'],$caption,$keyboard);
                }
                if (isset($mail[0]['gif']) && !is_null($mail[0]['gif'])) {
                    $caption = null;
                    sendAnimation($this->bot,$this->chat_id,$mail[0]['gif'],$caption,$keyboard);
                }
                if (isset($mail[0]['sticker']) && !is_null($mail[0]['sticker'])) {
                    sendSticker($this->bot,$this->chat_id,$mail[0]['sticker'],$keyboard);
                }
            }
        }

        public function menuCategory($id) {

            $main_array = [];
            $lng = getLang($this->user_data[0]['lang']);
            $category = $this->db->select("SELECT * FROM categories WHERE id='$id'");
            $categories = $this->db->select("SELECT * FROM categories WHERE parent_id='$id'");

            foreach ($categories as $ctgr) {
                $name = strip_tags($ctgr['name']);
                array_push($main_array, array(array('text'=> $name,'callback_data' => 'vieww.menuCategory.'.$ctgr['id'])));
            }

            if (isset($category[0]['parent_id'])) {
                array_push($main_array, array(array('text'=>"🔙 Назад",'callback_data' => 'vieww.menuCategory.'.$category[0]['parent_id'])));
            }else{
                array_push($main_array, array(array('text' => "🔙 Назад", 'callback_data' => 'view.menuTarifs')));
            }
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
            $this->db->update("UPDATE accounts SET menu='menu_category' WHERE chat_id='$this->chat_id'");

            if (isset($this->msg_id) && $this->msg_id > 0) {
                $this->bot->deleteMessage($this->chat_id,$this->msg_id);
                sendMessage($this->bot,$this->chat_id,$this->lang[$lng]['tarif_list'],$keyboard );
            }else{
                sendMessage($this->bot,$this->chat_id,$this->lang[$lng]['tarif_list'],$keyboard );
            }
        }
        
	}

