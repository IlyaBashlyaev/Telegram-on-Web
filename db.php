<?php
    if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.herokuapp.com')
        $connection = new mysqli('sql7.freesqldatabase.com', 'sql7530907', 'lfzWgEg88G', 'sql7530907');
    else
        $connection = new mysqli('127.0.0.1', 'Ilya Bashlyaev', '#vOV(0y2#vOV(0y2', 'chats-db');
?>