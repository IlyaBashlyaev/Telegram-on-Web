<?php
    if (isset($_COOKIE['id'])) {
        if ($_COOKIE['id'] == $_POST['authorId']) {
            $messageId = $_POST['messageId'];
            $connection = new mysqli('127.0.0.1', 'Ilya Bashlyaev', '#vOV(0y2#vOV(0y2', 'chats-db');

            $message = $connection -> query("SELECT * FROM `messages` WHERE `message-id` = '$messageId'")
                       -> fetch_assoc();
                       
            if ($message['type'] != 'text')
                unlink($message['file-name']);
            $connection -> query("DELETE FROM `messages` WHERE `message-id` = '$messageId'");
        }
    }
?>