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
