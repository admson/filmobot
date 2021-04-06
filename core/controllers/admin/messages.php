<?php
    
    function addFilmText($data) {
        global $db,$bot,$lang;
        if (isset($data['text'])) {
            $string = explode("\n", $data['text']);
            if (isset($string[0]) && isset($string[1])) {
                preg_match('#\\((.*?)\\)#', $string[0], $year);
                $start  = strpos($string[0], ',');
                $end    = strpos($string[0], '(', $start + 1);
                $length = $end - $start;
                $name = substr($string[0], $start + 1, $length - 1);
                $name = trim($name);

                $genres = preg_split('/[\s]+/', $string[1]);
                $ctgrs = [];
                foreach ($genres as $gen) {
                    $gen = str_replace("#", "", $gen);
                    if (isset($gen) && !empty($gen)) {
                        $db_gen = $db->select("SELECT * FROM categories WHERE name='$gen'");
                        if (!isset($db_gen[0]['id'])) {
                            $id = $db->insert("INSERT INTO categories(name) VALUES('$gen')");
                        }else{
                            $id = $db_gen[0]['id'];
                        }
                        array_push($ctgrs,$id);
                    }
                }

                if (isset($year[1]) && isset($name) && count($ctgrs) >= 1) {
                    $new_film = $db->insert("INSERT INTO films(text,name,year,categories) VALUES('".$data['text']."','$name','".$year[1]."','".json_encode($ctgrs)."')");
                    if (isset($new_film)) {
                        showRcp("add_film_photo", $data['chat_id'], false, false, $new_film);
                    }
                }else{
                    sendMessage($bot, $data['chat_id'], $lang['wrong_format']);
                }
            }else{
                sendMessage($bot, $data['chat_id'], $lang['wrong_format']);
            }
        }
    }

    function addFilmPhoto($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['photo'])) {
                $orig_file = $data['photo'][array_key_last($data['photo'])]->getFileId();
                $db->update("UPDATE films SET photo='".$orig_file."' WHERE id='$film_id'");
                showRcp("add_film_trailer", $data['chat_id'], false, false, $film_id);
            }
        }
    }

    function addFilmTrailer($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['video'])) {
                $orig_file = $data['video']->getFileId();
                $db->update("UPDATE films SET trailer='$orig_file' WHERE id='$film_id'");
                showRcp("add_film_video", $data['chat_id'], false, false, $film_id);
            }
        }
    }

    function addFilmVideo($data) {
        global $db,$bot,$lang;
        if (isset($data['dialog']['data'])) {
            $film_id = $data['dialog']['data'];
            if (isset($data['video'])) {
                $orig_file = $data['video']->getFileId();
                $db->update("UPDATE films SET video='$orig_file' WHERE id='$film_id'");
                sendMessage($bot, $data['chat_id'], $lang['success_create']);
                showRcp("admin", $data['chat_id'], false, false, $film_id);
            }
        }
    }