<?php
    $timezoneOffset = (int) $_GET['timezoneOffset'];
    $userTimezone = timezone_name_from_abbr("", $timezoneOffset, false);
    setcookie('user-timezone', $userTimezone, time() + 315360000);
?>