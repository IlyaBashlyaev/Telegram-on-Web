<?php
    require 'db.php';
    $symbols = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';

    if (!isset($_COOKIE['id']) && !isset($_COOKIE['guest-id'])) {
        $onlineUser = true;

        while ($onlineUser) {
            $id = '';
            for ($i = 0; $i < 11; $i++) {
                $id .= $symbols[rand(0, 61)];
            }

            $onlineUser = $connection -> query("SELECT * FROM `online-users` WHERE `id` = '$id'") -> fetch_assoc();
        }

        setcookie('guest-id', $id, time() + 315360000);
        $connection -> query("INSERT INTO `online-users` (`id`, `lastvisit`) VALUES ('$id', " . time() . ")");
    }

    else if (!isset($_COOKIE['id']) && isset($_COOKIE['guest-id'])) {
        $id = $_COOKIE['guest-id'];
        $onlineUser = $connection -> query("SELECT * FROM `online-users` WHERE `id` = '$id'") -> fetch_assoc();

        if (!$onlineUser)
            $connection -> query("INSERT INTO `online-users` (`id`, `lastvisit`) VALUES ('$id', " . time() . ")");
    }

    else if (isset($_COOKIE['id']) && isset($_COOKIE['guest-id'])) {
        $guestId = $_COOKIE['guest-id'];
        $id = $_COOKIE['id'];

        setcookie('guest-id', '', time() - 3600);
        $connection -> query("DELETE FROM `online-users` WHERE `id` = '$guestId'");
        $connection -> query("INSERT INTO `online-users` (`id`, `lastvisit`) VALUES ('$id', " . time() . ")");
    }

    else {
        $id = $_COOKIE['id'];
        $onlineUser = $connection -> query("SELECT * FROM `online-users` WHERE `id` = '$id'") -> fetch_assoc();

        if (!$onlineUser)
            $connection -> query("INSERT INTO `online-users` (`id`, `lastvisit`) VALUES ('$id', " . time() . ")");
    }

    $connection -> query('UPDATE `online-users` SET `lastvisit` = ' . time() . " WHERE `id` = '$id'");
    $onlineUsers = $connection -> query("SELECT * FROM `online-users` WHERE `id` NOT LIKE '$id'");

    while ($onlineUser = $onlineUsers -> fetch_assoc()) {
        if ($onlineUser['lastvisit'] <= time() - 2) {
            $connection -> query("DELETE FROM `online-users` WHERE `id` = '" . $onlineUser['id'] . "'");
        }
    }

    $onlineCount = $connection -> query('SELECT * FROM `online-users`') -> num_rows;
    echo $onlineCount;
?>