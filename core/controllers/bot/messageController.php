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
        global $admins;
        // Стандартные меню в боте
        $main_menu = [
            '⚙ Админ меню' => 'menuAdmin',
            '⚙ Admin Menu' => 'menuAdmin',
        ];
        // Админ меню в боте
        $admin_menu = [
            $this->lang['test'] => "menuQuiz",
        ];

        if (isset($main_menu[$this->text])) {
            call_user_func(array($this->view, $main_menu[$this->text]));
            exit();
        }
        if (in_array($this->chat_id,$admins)) {
            if (isset($admin_menu[$this->text])) {
                call_user_func(array($this->view, $admin_menu[$this->text]));
                exit();
            }
        }


        // Функции для текущего меню (user_data menu) без передачи id
        $allow_menus = [
        ];
        // Функции для текущего меню (user_data menu.$id) c передачей id
        $allow_menus_2 = [
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


}