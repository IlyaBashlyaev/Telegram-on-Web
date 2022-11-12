<?php
    if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.herokuapp.com')
        $connection = new mysqli('bav0fegt5cf833tuhm3z-mysql.services.clever-cloud.com', 'unhpmpdyf3fnpsgo', 'ieI44C230bJDDc1Rdtjg', 'bav0fegt5cf833tuhm3z');
    else
        $connection = new mysqli('127.0.0.1', 'Ilya Bashlyaev', '#vOV(0y2#vOV(0y2', 'chats-db');
?>