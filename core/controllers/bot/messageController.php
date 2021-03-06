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
        global $routes;
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

        $rpc = new Rpc();

        //Авторизация
        $user = new authController($this->chat_id, $this->username, $this->firstname, $this->lastname);
        $this->user_data = $user->authUser();

        // Если заблокирован
        if ($this->user_data[0]['status'] == 2) exit();
        $_dialogs = $db->select("SELECT * FROM _dialogs WHERE chat_id='".$this->chat_id."' ORDER BY created_at DESC LIMIT 1");

        // запоминаем данные и передаем в функцию
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $this->text,
            'photo' => $this->photo,
            'video' => $this->video,
        ];

        if (isset($_dialogs[0]['id'])) $data['dialog'] = $_dialogs[0];

        $role = getRole($this->chat_id);
        $routes = $role->routes;
        if (isset($routes[$this->user_data[0]['menu']]['message'])) {
            call_user_func(array($role, $routes[$this->user_data[0]['menu']]['message']), $data);
        }

        //Проверка стандатрных меню (Клавиатура)
        foreach ($routes as $key => $value) {
            if ($value['name'] == $this->text) {
                $rpc->show($key,$this->chat_id);
            }
        }
    }

}