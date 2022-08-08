<?php
    if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.herokuapp.com')
        $connection = new mysqli('sql11.freemysqlhosting.net', 'sql11511609', 'GdVVlkhKty', 'sql11511609');
    else
        $connection = new mysqli('127.0.0.1', 'Ilya Bashlyaev', '#vOV(0y2#vOV(0y2', 'chats-db');
?>