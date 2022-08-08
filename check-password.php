<?php
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
        
        if ($password == 'kwHK#EC9') {
            setcookie(
                'verification-key', 'q9ik15ob9ksgileaccroa5c80scffbv2', time() + 315360000
            );
            header('Location: /');
        }

        else
            header('Location: /?passwordError=true');
    }
?>