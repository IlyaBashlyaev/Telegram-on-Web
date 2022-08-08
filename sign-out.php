<?php
    if (isset($_COOKIE['id']))
        setcookie('id', '', time());
    header('Location: /')
?>