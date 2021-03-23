<?php

class messageController
{
    //Database
    private $db;
    private $dbconnection;

    //Main data
    public $bot;
    public $Message;
    public $lang;
    public $lng;

    //view
    public $view;

    //Input data
    public $text;
    public $photo;
    public $video;
    public $gif;
    public $sticker;
    public $caption;
    public $contact;

    //UserData
    public $chat_id;
    public $username;
    public $lastname;
    public $firstname;
    public $user_data;

    function __construct($bot, $db, $dbconnection, $Message, $lang)
    {
        $this->db = $db;
        $this->dbconnection = $dbconnection;
        $this->bot = $bot;
        $this->Message = $Message;
        $this->lang = $lang;

        // input data
        $this->text = $Message->getText();
        $this->photo = $Message->getPhoto();
        $this->video = $Message->getVideo();
        $this->gif = $Message->getAnimation();
        $this->sticker = $Message->getSticker();
        $this->caption = $Message->getCaption();
        $this->contact = $Message->getContact();

        // Информация о пользователе и его chat_id
        $this->firstname = $Message->getChat()->getFirstName();
        $this->lastname = $Message->getChat()->getLastName();
        $this->username = $Message->getChat()->getUsername();
        $this->chat_id = $Message->getChat()->getId();
        // Получаем язык
        $this->lng = getLang($Message->getFrom()->getLanguageCode());

        //Авторизация и обновления языка
        $user = new authController($this->chat_id, $this->username, $this->firstname, $this->lastname);
        $this->user_data = $user->authUser();
        // Если заблокирован
        if ($this->user_data[0]['status'] == 2) exit();

        // Вьюшки
        $this->view = new viewController($this->bot, $this->user_data, false, $db, $dbconnection, $lang);

        self::controller();

    }

    public function controller()
    {
        // Стандартные меню в боте
        $main_menu = [
            '⚙ Админ меню' => 'menuAdmin',
            '⚙ Admin Menu' => 'menuAdmin',
        ];
        // Админ меню в боте
        $admin_menu = [
            '⚙ Настройки' => 'menuSettings',
            '✉️ Рассылка в боте' => 'menuMail',
        ];

        if (isset($main_menu[$this->text])) {
            call_user_func(array($this->view, $main_menu[$this->text]));
            exit();
        }
        if ($this->user_data[0]['admin'] == 1) {
            if (isset($admin_menu[$this->text])) {
                call_user_func(array($this->view, $admin_menu[$this->text]));
                exit();
            }
        }


        // Функции для текущего меню (user_data menu) без передачи id
        $allow_menus = [
            // Send Contact
            'send_contact' => 'sendContact',
            // Меню рассылки
            'add_url_buttons' => 'addUrlButtons',
            'addmail' => 'newMail',
            'edit_mail_text' => 'editMailText',
            'edit_mail_caption' => 'editMailCaption',
            'add_media' => 'addMedia',
        ];
        // Функции для текущего меню (user_data menu.$id) c передачей id
        $allow_menus_2 = [
            'test' => 'test',
        ];

        if (isset($allow_menus[$this->user_data[0]['menu']])) {
            call_user_func("self::" . $allow_menus[$this->user_data[0]['menu']]);
            exit();
        }
        $data = explode(".", $this->user_data[0]['menu']);
        if (isset($allow_menus_2[$data[0]]) && isset($data[1])) {
            call_user_func("self::" . $allow_menus_2[$data[0]], $data[1]);
            exit();
        }
    }

    public function sendContact()
    {
        if (isset($this->contact) && !empty($this->contact)) {
            $phone_number = $this->contact->getPhoneNumber(); // or 'html'
            $phone_number = str_replace("+", "", $phone_number);
            $this->db->update("UPDATE accounts SET contact='" . $phone_number . "' WHERE chat_id='" . $this->chat_id . "'");
            $country_code = mb_strcut($phone_number, 0, 3);
            sendMessage($this->bot, $this->chat_id, $phone_number." ".$country_code);
            if ($country_code == "998" || $country_code == "790" || $country_code == "380") {
                $view = $this->view->menuMain();
            }else{
                sendMessage($this->bot, $this->chat_id, $this->lang[$this->user_data[0]['lang']]['send_contact_error']);
                $this->db->update("UPDATE accounts SET status='2' WHERE chat_id='" . $this->chat_id . "'");
            }
        }
    }

    // Рассылка

    public function newMail()
    {
        $created = false;
        $isset = $this->db->select("SELECT * FROM mail WHERE author_id='" . $this->chat_id . "' AND status=0");
        if (isset($isset[0]['id'])) {
            $this->db->delete("DELETE FROM mail WHERE id='" . $isset[0]['id'] . "'");
        }
        $id = $this->db->insert("INSERT INTO mail(author_id) VALUES ('" . $this->chat_id . "')");
        if (isset($this->text) && !empty($this->text)) {
            $entity_decoder = new EntityDecoder('HTML'); // or 'html'
            $decoded_text = $entity_decoder->decode($this->Message);
            $this->db->update("UPDATE mail SET text='" . $decoded_text . "' WHERE id='" . $id . "'");
            $created = true;
        }
        if (isset($this->photo)) {
            $orig_file = $this->photo[array_key_last($this->photo)]->getFileId();
            $this->db->update("UPDATE mail SET photo='" . $orig_file . "' WHERE id='" . $id . "'");
            $created = true;
        }
        if (isset($this->video)) {
            $orig_file = $this->video->getFileId();
            $this->db->update("UPDATE mail SET video='" . $orig_file . "' WHERE id='" . $id . "'");
            $created = true;
        }
        if (isset($this->gif)) {
            $orig_file = $this->gif->getFileId();
            $this->db->update("UPDATE mail SET gif='" . $orig_file . "' WHERE id='" . $id . "'");
            $created = true;
        }
        if (isset($this->sticker)) {
            $orig_file = $this->sticker->getFileId();
            $this->db->update("UPDATE mail SET sticker='" . $orig_file . "' WHERE id='" . $id . "'");
            $created = true;
        }
        if (isset($this->caption)) {
            $entity_decoder = new EntityDecoder('HTML'); // or 'html'
            $decoded_text = $entity_decoder->decode($this->Message);
            $this->db->update("UPDATE mail SET caption='" . $decoded_text . "' WHERE id='" . $id . "'");
        }

        if ($created == true) {
            $mail = $this->db->select("SELECT * FROM mail WHERE id='" . $id . "'");
            $view = $this->view->menuEditMail($mail[0]['id']);
        }
    }

    public function addMedia()
    {
        global $botToken;
        $created = false;
        if (isset($this->photo)) {
            $orig_file = $this->photo[array_key_last($this->photo)]->getFileId();
            $created = true;
        }
        if (isset($this->video)) {
            $orig_file = $this->video->getFileId();
            $created = true;
        }
        if (isset($this->gif)) {
            $orig_file = $this->gif->getFileId();
            $created = true;
        }

        if ($created == true) {
            if (!is_dir("./bots/" . $this->chat_id)) {
                mkdir("./bots");
                mkdir("./bots/" . $this->chat_id);
                mkdir("./bots/" . $this->chat_id . "/animations");
                mkdir("./bots/" . $this->chat_id . "/videos");
                mkdir("./bots/" . $this->chat_id . "/photos");
            }
            $file = $this->bot->getFile($orig_file);
            $file_path = $file->getFilePath();
            if (isset($file_path)) {
                $file_url = "https://api.telegram.org/file/bot" . $botToken[0]['bot_token'] . "/" . $file_path;
                $content = file_get_contents($file_url);
                $path_info = pathinfo($file_path);
                $new_path = $path_info['dirname']."/".mt_rand(1111111,9999999).".".$path_info['extension'];
                file_put_contents("./bots/" . $this->chat_id . "/" . $new_path, $content);
            }
            $this->db->update("UPDATE mail SET media='$new_path' WHERE author_id='" . $this->chat_id . "'");
            $view = $this->view->menuEditMail();
        }
    }

    public function editMailText()
    {
        if (isset($this->text) && !empty($this->text)) {
            $entity_decoder = new EntityDecoder('HTML'); // or 'html'
            $decoded_text = $entity_decoder->decode($this->Message);
            $this->db->update("UPDATE mail SET text='" . $decoded_text . "' WHERE author_id='" . $this->chat_id . "'");
            $view = $this->view->menuEditMail();
        }
    }

    public function editMailCaption()
    {
        if (isset($this->text) && !empty($this->text)) {
            $entity_decoder = new EntityDecoder('HTML'); // or 'html'
            $decoded_text = $entity_decoder->decode($this->Message);
            $this->db->update("UPDATE mail SET caption='" . $decoded_text . "' WHERE author_id='" . $this->chat_id . "'");
            $view = $this->view->menuEditMail();
        }
    }

    public function addUrlButtons()
    {
        if (isset($this->text) && !empty($this->text)) {
            $array_buttons = [];
            $text_rows = explode("\n", $this->text);
            foreach ($text_rows as $row) {
                $text_col = explode("|", $row);
                $array_row = [];
                foreach ($text_col as $col) {
                    $button = explode("-", $col, 2);
                    if (count($button) > 1) {
                        $button[1] = trim($button[1]);
                        $button[0] = trim($button[0]);

                        if (filter_var(idn_to_ascii($button[1]), FILTER_VALIDATE_URL)) {
                            $button = array('text' => $button[0], 'url' => $button[1]);
                            array_push($array_row, $button);
                        }
                    }
                }
                if (count($array_row) >= 1) {
                    array_push($array_buttons, $array_row);
                }
            }
            if (count($array_buttons) >= 1 && count($array_buttons) <= 10) {
                $array_buttons = json_encode($array_buttons);
                $this->db->update("UPDATE mail SET url_buttons='" . mysqli_real_escape_string($this->dbconnection, $array_buttons) . "' WHERE author_id='" . $this->chat_id . "'");
                $view = $this->view->menuEditMail();
            } else {
                sendMessage($this->bot, $this->chat_id, "Отправьте боту список в правильном формате! 👇\n\n<i>Кнопка 1 - https://domain.com/</i>");
            }
        }
    }
}