<?php

    // Класс который рисует менюшки
    // $hash - либо хеш либо название menu
    // $chat_id - чат id аккаунит
    // inline_keyboard,$msg_id,$data($id фильма,категории и т.д) - не обязательные параметры

    function showRpc($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1) {
        $rpc = new Rpc();
        $rpc->show($hash,$chat_id,$keyboard,$msg_id,$data,$page);
    }

    class Rpc {

        // Данные которые будут доступны в функциях
        public $db;
        public $dbconnection;
        public $bot;
        public $lang;
        public $routes;

        // Конструктор
        public function __construct(){
            global $db,$dbconnection,$bot,$lang,$routes;

            $this->db = $db;
            $this->dbconnection = $dbconnection;
            $this->bot = $bot;
            $this->lang = $lang;
            $this->routes = $routes;
        }

        // Отрисовка меню
        public function show($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1,$paginator = false) {
            self::deleteTimeout();

            //
            if (!isset($this->routes[$hash])) {
                $find_hash = $this->db->select("SELECT * FROM dialogs WHERE hash='$hash'");
                if (isset($find_hash[0]['id'])) {
                    $hash = $find_hash[0]['menu'];
                    $data = $find_hash[0]['data'];
                }
            }
            if (isset($this->routes[$hash])) {
                $state_hash = md5($hash.mt_rand(1111,9999));
                $bot_username = $this->bot->getMe()->getUsername();
                $this->db->update("UPDATE _accounts SET menu='$hash' WHERE chat_id='$chat_id'");
                //Чистим историю и создаем новый хеш
                if (isset($this->routes[$hash]['clean_cache'])) $this->db->delete("DELETE FROM dialogs WHERE chat_id='$chat_id'");
                if (!$paginator) {
                    $new_state = $this->db->insert("INSERT INTO dialogs(hash,chat_id,menu) VALUES('$state_hash','".$chat_id."','".$hash."')");
                }else{
                    $this->db->update("UPDATE dialogs SET page='$page' WHERE id='".$paginator[0]['id']."'");
                    $new_state = $paginator[0]['id'];
                }
                if (isset($data) && $data >= 1 && !isset($this->routes[$hash]['clean_cache'])) $this->db->update("UPDATE dialogs SET data='$data' WHERE id='$new_state'");

                // добавляем клавиатуру
                if (isset($this->routes[$hash]['inline_keyboard'])) {
                    $kbarray = $this->routes[$hash]['inline_keyboard'];
                }else{
                    $kbarray = [];
                }

                //Добавляем клавиатуру из функции
                if (isset($this->routes[$hash]['keyboard_func'])) {
                    if (isset($page) && $page > 1) $this->db->update("UPDATE dialogs SET page='$page' WHERE id='$new_state'");
                    $role = getRole($chat_id);
                    if (isset($data)) {
                        $kbfunc = call_user_func(array($role,$this->routes[$hash]['keyboard_func']),$page,$data);
                    }else{
                        $kbfunc = call_user_func(array($role,$this->routes[$hash]['keyboard_func']),$page);
                    }


                    $kbarray = array_merge($kbarray,$kbfunc);
                }

                // Хлебные крошки и кнопки Назад, отмена
                $breads = "";
                if (!isset($this->routes[$hash]['clean_cache'])) {
                    $dialogs_bread = $this->db->select("SELECT * FROM dialogs WHERE chat_id='$chat_id' ORDER BY created_at");
                    foreach ($dialogs_bread as $state) {
                        if (isset($this->routes[$state['menu']]) && !isset($this->routes[$state['menu']]['clean_cache'])) $breads.= "<i>/<a href='https://t.me/".$bot_username."?start=".$state['hash']."'>".$this->routes[$state['menu']]['name']."</a></i>";
                    }
                }

                // Назад и отмена
                if (!isset($this->routes[$hash]['clean_cache'])) {
                    $prev_menu = $this->routes[$hash]['prev_menu'];
                    array_push($kbarray, array(array('text'=> $this->lang['back'],'callback_data' => "prew.".$prev_menu),array('text'=> $this->lang['cancel'],'callback_data' => "view.admin")));
                }

                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kbarray);

                // создаем ответ
                $answer = $this->routes[$hash]['answer'];
                if (!empty($breads)) $answer = $breads."\n\n".$this->routes[$hash]['answer'];

                // Отправляем либо редактируем сообщение
                self::sendMsg($chat_id,$msg_id, $answer,$keyboard);
            }
        }

        // Отправка либо редактирование сообщения
        public function sendMsg($chat_id,$msg_id = false, $answer,$keyboard) {
            if (isset($msg_id) && $msg_id > 0) {
                editMessage($this->bot,$chat_id,$msg_id,$answer,$keyboard);
            }else{
                sendMessage($this->bot,$chat_id,$answer,$keyboard );
            }
        }

        // Удаление по таймауту
        public function deleteTimeout() {
            // Удаление активностей, историю пользователя
            $now_time = new DateTime('now');
            $now_time->modify("-".SESSION_TIMEOUT." minutes");
            $this->db->delete("DELETE FROM dialogs WHERE created_at <= '".$now_time->format('Y-m-d H:i:s')."'");
            // Удаление фильмов по таймауту
            $now_time = new DateTime('now');
            $now_time->modify("-".FILM_TIMEOUT." minutes");
            $this->db->delete("DELETE FROM films WHERE created_at <= '".$now_time->format('Y-m-d H:i:s')."' AND hash IS NULL");
        }

    }