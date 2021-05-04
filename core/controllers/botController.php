<?php
function answerCallbackQuery($bot,$id,$text = null,$alert = false){
    try {
        return $bot->answerCallbackQuery($id, $text, $alert);
    } catch (TelegramBot\Api\HttpException $e) {
        return false;
    }
}

function sendMessage($bot,$chat_id,$message,$keyboard = null,$parse_mode = "html",$disablePreview = false,$notif = false) {
    try {
        return $bot->sendMessage($chat_id, $message, $parse_mode, $disablePreview, null, $keyboard, $notif);
    } catch (TelegramBot\Api\HttpException $e) {
        return false;
    }
}

function editMessage($bot,$chat_id,$msg_id,$message,$keyboard = null,$parse_mode = "html",$disablePreview = false) {
    try {
        return $bot->editMessageText($chat_id,$msg_id,$message,$parse_mode,$disablePreview,$keyboard);
    } catch (TelegramBot\Api\HttpException $e) {
        return false;
    }
}

function sendPhoto($bot,$chat_id,$file_id,$caption = null,$keyboard = null,$disableNotification = false) {
    $replyToMessageId = null;
    $parseMode = "html";

    try {
        return $bot->sendPhoto($chat_id,$file_id,$caption,$replyToMessageId,$keyboard,$disableNotification,$parseMode);;
    } catch (TelegramBot\Api\HttpException $e) {
        return false;
    }
}
function sendVideo($bot,$chat_id,$file_id,$caption = null,$keyboard = null,$disableNotification = false) {
    $duration = null;
    $replyToMessageId = null;
    $supportsStreaming = false;
    $parseMode = "html";

    try {
        return $bot->sendVideo($chat_id,$file_id,$duration,$caption,$replyToMessageId,$keyboard,$disableNotification,$supportsStreaming,$parseMode);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function sendAnimation($bot,$chat_id,$file_id,$caption = null,$keyboard = null,$disableNotification = false) {
    $duration = null;
    $replyToMessageId = null;
    $parseMode = "html";

    try {
        return $bot->sendAnimation($chat_id,$file_id,$duration,$caption,$replyToMessageId,$keyboard,$disableNotification,$parseMode);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function sendSticker($bot,$chat_id,$file_id,$keyboard = null,$disableNotification = false) {
    $replyToMessageId = null;

    try {
        return $bot->sendSticker($chat_id,$file_id,$replyToMessageId,$keyboard,$disableNotification);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function sendVoice($bot,$chat_id,$file_id,$keyboard = null, $disableNotification = false) {
    $duration = null;
    $replyToMessageId = null;
    $parseMode = null;

    try {
        return $bot->sendVoice($chat_id,$file_id,$duration,$replyToMessageId,$keyboard,$disableNotification,$parseMode);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function sendDocument($bot,$chat_id,$file_id,$caption,$replyMarkup = null, $disableNotification = false) {
    $replyToMessageId = null;

    try {
        return $bot->sendDocument($chat_id,$file_id,$caption,$replyToMessageId,$replyMarkup,$disableNotification,"html");
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function sendMediaGroup($bot,$chat_id,$media,$disableNotification = false) {
    $replyToMessageId = null;
    try {
        return $bot->sendMediaGroup($chat_id,$media,$disableNotification,$replyToMessageId);
    } catch (TelegramBot\Api\HttpException $e) {
        return false;
    }
}


function editMessageCaption($bot,$chat_id,$msg_id,$caption,$keyboard = null) {
    try {
        $bot->editMessageCaption($chat_id,$msg_id,$caption,$keyboard);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}

function editMessageReplyMarkup($bot,$chat_id,$msg_id,$keyboard = null) {
    try {
        $bot->editMessageReplyMarkup($chat_id,$msg_id,$keyboard);
    } catch (TelegramBot\Api\HttpException $e) {
        exit();
    }
}


function sendChatMessage($bot,$post) {
    global $dbb;
    if (isset($post['id'])) {
        $main_array = [];
        if (isset($post['url_buttons'])) {
            $url_buttons = json_decode($post['url_buttons'], true);
            foreach ($url_buttons as $button) {
                array_push($main_array, $button);
            }
        }
        if (isset($post['reactions'])) {
            $reactions = json_decode($post['reactions'], true);
            foreach ($reactions as $key => $react) {
                $row = [];
                foreach ($react as $k => $btn) {
                    $insert_id = $dbb->insert("INSERT INTO reactions(post_id,row,button) VALUES('" . $post['id'] . "','$key','$btn')");
                    array_push($row, array('text' => $btn, 'callback_data' => 'reaction.' . $insert_id));
                }
                if (count($row) >= 1) {
                    array_push($main_array, $row);
                }
            }
        }
        if (isset($post['hidden_msg_button']) && isset($post['hidden_msg_answer']) && isset($post['hidden_sub_success'])) {
            $hidden_button = array(array('text' => $post['hidden_msg_button'], 'callback_data' => 'hidden_button.' . $post['id']));
            array_push($main_array, $hidden_button);
        }
        $caption = null;
        $keyboard = null;
        $preview = false;
        $notif = false;
        if (isset($post['caption'])) $caption = $post['caption'];
        if (isset($post['preview']) && $post['preview'] == 0) $preview = true;
        if (isset($post['notification']) && $post['notification'] == 0) $notif = true;
        if (!empty($main_array)) $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
        $chat = $dbb->select("SELECT * FROM chats WHERE id='" . $post['chat_id'] . "'");
        if (!isset($post['media_group_id'])) {
            if (isset($post['text']) && !is_null($post['text'])) {
                if (isset($post['media'])) {
                    $file_url = "https://admaker.tech/bot/bots/" . $post['author_id'] . "/" . $post['media'];
                    $post['text'] .= "<a href='$file_url'>&#8205;</a>";
                }
                $message = sendMessage($bot, $chat[0]['chat_id'], $post['text'], $keyboard, "html", $preview, $notif);
            }
            if (isset($post['photo']) && !is_null($post['photo'])) {
                $message = sendPhoto($bot, $chat[0]['chat_id'], $post['photo'], $caption, $keyboard, $notif);
            }
            if (isset($post['video']) && !is_null($post['video'])) {
                $message = sendVideo($bot, $chat[0]['chat_id'], $post['video'], $caption, $keyboard, $notif);
            }
            if (isset($post['gif']) && !is_null($post['gif'])) {
                $message = sendAnimation($bot, $chat[0]['chat_id'], $post['gif'], $caption, $keyboard, $notif);
            }
            if (isset($post['sticker']) && !is_null($post['sticker'])) {
                $message = sendSticker($bot, $chat[0]['chat_id'], $post['sticker'], $keyboard, $notif);
            }
            if (isset($post['voice']) && !is_null($post['voice'])) {
                $message = sendVoice($bot, $chat[0]['chat_id'], $post['voice'], $keyboard, $notif);
            }
            if (isset($post['document']) && !is_null($post['document'])) {
                $message = sendDocument($bot, $chat[0]['chat_id'], $post['document'], $caption, $keyboard, $notif);
            }
            $msg_id = $message->getMessageId();
        }
        if (isset($post['media_group_id']) && !is_null($post['media_group_id'])) {
            $message = sendMediaGroup($bot, $chat[0]['chat_id'], $post['media_group_id'], $post['caption'], $notif);
            $msg_id = $message[0]->getMessageId();
        }
        if (isset($post['pin_message']) && $post['pin_message'] == 1) {
            $bot->pinChatMessage($chat[0]['chat_id'],$msg_id,$notif);
        }
        $dbb->update("UPDATE ready_posts SET msg_id='$msg_id' WHERE id='".$post['id']."'");
        $dbb->update("UPDATE ready_posts SET status='1' WHERE id='".$post['id']."'");
    }
}

function updateChatMessage($bot,$post,$msg_id) {
    global $dbb;
    if (isset($post[0]['id'])) {
        $main_array = [];
        if (isset($post[0]['url_buttons'])) {
            $url_buttons = json_decode($post[0]['url_buttons'], true);
            foreach ($url_buttons as $button) {
                array_push($main_array, $button);
            }
        }
        if (isset($post[0]['reactions'])) {
//                $reactions = json_decode($post[0]['reactions'], true);
            $reactions = $dbb->select("SELECT * FROM reactions WHERE post_id='".$post[0]['id']."'");
            $react_buttons = array();
            foreach ($reactions as $react) {
                $reacts = $dbb->count("SELECT COUNT(1) FROM reacts WHERE reaction_id='".$react['id']."'");
                if ($reacts == 0) $reacts = "";
                if (!isset($react_buttons[$react['row']])) {
                    $react_buttons[$react['row']] = [];
                    array_push($react_buttons[$react['row']], array('text' => $react['button']." ".$reacts, 'callback_data' => 'reaction.' . $react['id']));
                }else{
                    array_push($react_buttons[$react['row']], array('text' => $react['button']." ".$reacts, 'callback_data' => 'reaction.' . $react['id']));
                }
            }
            foreach ($react_buttons as $item) {
                array_push($main_array, $item);
            }
        }
        if (isset($post[0]['hidden_msg_button']) && isset($post[0]['hidden_msg_answer']) && isset($post[0]['hidden_sub_success'])) {
            $hidden_button = array(array('text' => $post[0]['hidden_msg_button'], 'callback_data' => 'hidden_button.' . $post[0]['id']));
            array_push($main_array, $hidden_button);
        }
        $caption = null;
        $keyboard = null;
        if (isset($post[0]['caption'])) $caption = $post[0]['caption'];
        if (!empty($main_array)) $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
        $chat = $dbb->select("SELECT * FROM chats WHERE id='".$post[0]['chat_id']."'");
        if (isset($post[0]['text']) && !is_null($post[0]['text'])) {
            if (isset($post[0]['media'])) {
                $file_url = "https://admaker.tech/bot/bots/" . $post[0]['author_id'] . "/" . $post[0]['media'];
                $post[0]['text'] .= "<a href='$file_url'>&#8205;</a>";
            }
            editMessage($bot, $chat[0]['chat_id'], $msg_id, $post[0]['text'], $keyboard);
        }
        if (isset($post[0]['photo']) && !is_null($post[0]['photo'])) {
            editMessageCaption($bot,$chat[0]['chat_id'],$post[0]['msg_id'],$caption,$keyboard);
        }
        if (isset($post[0]['video']) && !is_null($post[0]['video'])) {
            editMessageCaption($bot, $chat[0]['chat_id'], $post[0]['msg_id'], $caption, $keyboard);
        }
        if (isset($post[0]['gif']) && !is_null($post[0]['gif'])) {
            editMessageCaption($bot, $chat[0]['chat_id'], $post[0]['msg_id'], $caption, $keyboard);
        }
        if (isset($post[0]['sticker']) && !is_null($post[0]['sticker'])) {
            editMessageReplyMarkup($bot, $chat[0]['chat_id'], $post[0]['msg_id'], $keyboard);
        }
    }
}

function sendMail($bot,$chat_id,$menu) {
    $main_array = [];
    if (isset($menu[0]['url_buttons'])) {
        $url_buttons = json_decode($menu[0]['url_buttons'], true);
        foreach ($url_buttons as $button) {
            array_push($main_array, $button);
        }
    }
    $keyboard = null;
    if (!empty($main_array)) $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($main_array);
    $caption = null;
    if (isset($menu[0]['caption'])) $caption = $menu[0]['caption'];
    if (isset($menu[0]['text']) && !is_null($menu[0]['text'])) {
        if (isset($menu[0]['media'])) {
            $file_url = "https://admaker.tech/bot/bots/".$menu[0]['author_id']."/".$menu[0]['media'];
            $menu[0]['text'].= "<a href='$file_url'>&#8205;</a>";
        }

        try {
            return $bot->sendMessage($chat_id, $menu[0]['text'], "html", false, null, $keyboard);
        } catch (TelegramBot\Api\HttpException $e) {
            return "banned";
        }
    }
    if (isset($menu[0]['photo']) && !is_null($menu[0]['photo'])) {
        $replyToMessageId = null;
        $disableNotification = false;
        $parseMode = "html";

        try {
            return $bot->sendPhoto($chat_id,$menu[0]['photo'],$caption,$replyToMessageId,$keyboard,$disableNotification,$parseMode);;
        } catch (TelegramBot\Api\HttpException $e) {
            return "banned";
        }
    }
    if (isset($menu[0]['video']) && !is_null($menu[0]['video'])) {
        $duration = null;
        $replyToMessageId = null;
        $disableNotification = false;
        $supportsStreaming = false;
        $parseMode = "html";

        try {
            return $bot->sendVideo($chat_id,$menu[0]['video'],$duration,$caption,$replyToMessageId,$keyboard,$disableNotification,$supportsStreaming,$parseMode);
        } catch (TelegramBot\Api\HttpException $e) {
            return "banned";
        }
    }
    if (isset($menu[0]['gif']) && !is_null($menu[0]['gif'])) {
        $duration = null;
        $replyToMessageId = null;
        $disableNotification = false;
        $parseMode = "html";

        try {
            return $bot->sendAnimation($chat_id,$menu[0]['gif'],$duration,$caption,$replyToMessageId,$keyboard,$disableNotification,$parseMode);
        } catch (TelegramBot\Api\HttpException $e) {
            return "banned";
        }
    }
    if (isset($menu[0]['sticker']) && !is_null($menu[0]['sticker'])) {
        $replyToMessageId = null;
        $disableNotification = false;

        try {
            return $bot->sendSticker($chat_id,$menu[0]['sticker'],$replyToMessageId,$keyboard,$disableNotification);
        } catch (TelegramBot\Api\HttpException $e) {
            return "banned";
        }
    }
}

