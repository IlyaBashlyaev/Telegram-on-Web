<?php
    if (isset($_COOKIE['id'])) {
        if ($_COOKIE['id'] == $_POST['authorId']) {
            require 'db.php';

            $messageId = $_POST['messageId'];
            $message = $connection -> query("SELECT * FROM `messages` WHERE `message-id` = '$messageId'")
                       -> fetch_assoc();
                       
            if ($message['type'] != 'text')
                unlink($message['file-name']);
            $connection -> query("DELETE FROM `messages` WHERE `message-id` = '$messageId'");
        }
    }
?>