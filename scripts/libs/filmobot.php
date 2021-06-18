<?php
    // Bot statistics lib

        function createMediaGroup($film,$card = false) {
            $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
            if (isset($film[0]['photo'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto($film[0]['photo'],$film[0]['text'],"html"));
            }
            if (isset($film[0]['trailer'])) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['trailer']));
            }
            if (isset($film[0]['video']) && !$card) {
                $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaVideo($film[0]['video']));
            }

            return $media;
        }

        // Функция получения хеш-тегов
        function getHashtags($string) {
            $hashtags= FALSE;
            preg_match_all("/(#\w+)/u", $string, $matches);
            if ($matches) {
                $hashtagsArray = array_count_values($matches[0]);
                $hashtags = array_keys($hashtagsArray);
            }
            return $hashtags;
        }