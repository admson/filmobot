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

        // Некоторые переменные
        public $new_state;

        // Конструктор
        public function __construct(){
            global $db,$dbconnection,$bot,$lang;

            $this->db = $db;
            $this->dbconnection = $dbconnection;
            $this->bot = $bot;
            $this->lang = $lang;
        }

        // Edit Markup (редактирование клавиатуры)
        public function updateMarkup($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1,$paginator = false)
        {
            $role = getRole($chat_id);
            $this->routes = $role->routes;
            $route = $this->routes[$hash];
            $kbarray = $this->createMarkup($chat_id,$route,$data,$page);
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kbarray);
            editMessageReplyMarkup($this->bot,$chat_id,$msg_id,$keyboard);
        }

        // Отрисовка меню
        public function show($hash,$chat_id,$keyboard = false,$msg_id = false,$data = false,$page = 1,$paginator = false) {
            //Поиск хеша и определенеие роли человека
            $role = getRole($chat_id);
            $this->routes = $role->routes;

            if (!isset($this->routes[$hash])) {
                // Поиск хешей
                $find_hash = $this->db->select("SELECT * FROM _dialogs WHERE hash='$hash'");
                if (isset($find_hash[0]['id'])) {
                    $route = $this->routes[$find_hash[0]['menu']];
                    $data = $find_hash[0]['data'];
                    $hash = $find_hash[0]['menu'];
                }
                // Поиск кастомных сюжетных хешей
                $custom_hash = call_user_func(array($role,'customHash'),$hash);;
                if (isset($custom_hash[0]) && isset($custom_hash[1])) {
                    $route = $this->routes[$custom_hash[0]];
                    $data = $custom_hash[1];
                    $hash = $custom_hash[0];
                }
            }else{
                $route = $this->routes[$hash];
            }
            
            //Удаление по таймауту
            self::deleteTimeout();

            if (isset($route)) {
                $state_hash = md5($hash.mt_rand(1111,9999)); // Получаем новый хеш
                $this->db->update("UPDATE _accounts SET menu='$hash' WHERE chat_id='$chat_id'");
                $bot_username = $this->bot->getMe()->getUsername();
                //Чистим историю и создаем новый хеш
                if (isset($route['clean_cache']) && !$paginator) $this->db->delete("DELETE FROM _dialogs WHERE chat_id='$chat_id'");
                if (!$paginator) {
                    $this->new_state = $this->db->insert("INSERT INTO _dialogs(hash,chat_id,menu) VALUES('$state_hash','".$chat_id."','".$hash."')");
                }else{
                    $this->db->update("UPDATE _dialogs SET page='$page' WHERE id='".$paginator[0]['id']."'");
                    $this->new_state = $paginator[0]['id'];
                }
                if (isset($data) && $data >= 1 && !isset($route['clean_cache'])) $this->db->update("UPDATE _dialogs SET data='$data' WHERE id='$this->new_state'");

                // Добавляем клавиатуру
                $kbarray = $this->createMarkup($chat_id,$route,$data,$page);
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($kbarray);

                // Хлебные крошки
                $breads = "";
                if (!isset($route['clean_cache'])) {
                    $_dialogs_bread = $this->db->select("SELECT * FROM _dialogs WHERE chat_id='$chat_id' ORDER BY created_at");
                    foreach ($_dialogs_bread as $state) {
                        if (isset($this->routes[$state['menu']]) && !isset($this->routes[$state['menu']]['clean_cache'])) $breads.= "<i>/<a href='https://t.me/".$bot_username."?start=".$state['hash']."'>".$this->routes[$state['menu']]['name']."</a></i>";
                    }
                }

                // создаем ответ
                if (isset($route['answer'])) {
                    $answer = $route['answer'];
                    if (!empty($breads)) $answer = $breads . "\n\n" . $route['answer'];
                }

                // Отправляем либо редактируем сообщение
                if (!isset($route['view_func']) && !isset($route['lib_func'])) {
                    self::sendMsg($chat_id, $msg_id, $answer, $keyboard);
                }else{
                    if (isset($route['view_func'])) {
                        $role = getRole($chat_id);
                        call_user_func(array($role, $route['view_func']), $data, $chat_id, $breads, $keyboard);
                    }
                    if (isset($route['lib_func'])) {
                        $lib_func = explode("::",$route['lib_func']);
                        $func = new $lib_func[0];
                        call_user_func(array($func, $lib_func[1]), $data, $chat_id, $breads, $keyboard);
                    }
                }
            }
        }

        // Create Keyboard
        public function createMarkup($chat_id,$route,$data,$page) {
            $role_s = getRole($chat_id,true);
            //Клавиатура из routes
            if (isset($route['inline_keyboard'])) {
                $kbarray = $route['inline_keyboard'];
            }else{
                $kbarray = [];
            }

            //Добавляем клавиатуру из функции
            if (isset($route['keyboard_func'])) {
                if (isset($page) && $page > 1) $this->db->update("UPDATE _dialogs SET page='$page' WHERE id='$this->new_state'");
                $role = getRole($chat_id);
                if (isset($data)) {
                    $kbfunc = call_user_func(array($role,$route['keyboard_func']),$page,$data);
                }else{
                    $kbfunc = call_user_func(array($role,$route['keyboard_func']),$page);
                }
                $kbarray = array_merge($kbarray,$kbfunc);
            }

            //Кнопки удаления
            if (isset($route['delete_btn'])) {
                array_push($kbarray, array(array('text'=> $this->lang['delete'],'callback_data' => "delete.".$data)));
            }

            //Реакции
            if (isset($route['reactions'])) {
                $react_1 = $this->db->count("SELECT COUNT(1) FROM _reactions WHERE item_id='$data' AND react=1");
                $react_2 = $this->db->count("SELECT COUNT(1) FROM _reactions WHERE item_id='$data' AND react=2");
                array_push($kbarray, array(array('text'=> $this->lang['like']." ".$react_1,'callback_data' => "reaction.".$data.".1"),array('text'=> $this->lang['dislike']." ".$react_2,'callback_data' => "reaction.".$data.".2")));
            }

            // Назад и отмена
            if (!isset($route['clean_cache'])) {
                $prev_menu = $route['prev_menu'];
                if ($prev_menu) {
                    array_push($kbarray, array(array('text'=> $this->lang['back'],'callback_data' => "prew.".$prev_menu),array('text'=> $this->lang['cancel'],'callback_data' => "view.".$role_s)));
                }else{
                    array_push($kbarray, array(array('text'=> $this->lang['cancel'],'callback_data' => "view.".$role_s)));
                }
            }

            return $kbarray;
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
            $this->db->delete("DELETE FROM _dialogs WHERE created_at <= '".$now_time->format('Y-m-d H:i:s')."'");
        }

        // Функция возвращения на предыдущее меню
        public function prewMenu($chat_id,$menu,$msg_id = false) {
            $role = getRole($chat_id);
            $this->routes = $role->routes;

            $dialog = $this->db->select("SELECT * FROM _dialogs WHERE chat_id='".$chat_id."' ORDER BY created_at DESC LIMIT 2");
            $data2 = false;
            if (isset($dialog[1]['data'])) $data2 = $dialog[1]['data'];
            $page = 1;
            if (isset($dialog[1]['page'])) $page = $dialog[1]['page'];
            foreach ($dialog as $diag) {
                $this->db->delete("DELETE FROM _dialogs WHERE id='".$diag['id']."'");
            }
            if (isset($this->routes[$dialog[0]['menu']]['view_func'])) $msg_id = false;
            $this->show($menu,$chat_id,false, $msg_id,$data2,$page);
        }

        // Функция получения предыдущего меню
        public function getPrewMenu($chat_id) {
            $dialog = $this->db->select("SELECT * FROM _dialogs WHERE chat_id='".$chat_id."' ORDER BY created_at DESC LIMIT 2");
            if (isset($dialog[1]['menu'])) {
                return $dialog[1]['menu'];
            }else{
                return false;
            }
        }

    }