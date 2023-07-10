<?php
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'PHPMailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous">

        <link rel="shortcut icon" href="images/icon.png">
        <link rel="stylesheet" href="style.css">
        <title>Fresh and Tasty - Registration</title>
    </head>

    <body>
        <div class="alert" style='opacity: 0; z-index: 0;'>
            <div class="inner-alert">
                <div class="alert-text"></div>
                <input type="text" class="inner-form-input code" onclick="wrongClick(this)" style='display: none' placeholder="Verification code">
                <div class="alert-button">
                    <button onclick='removeAlert()'>Ok</button>
                </div>
            </div>
        </div>

        <div class="theme-block">
            <i class="far fa-sun"></i>
        </div>

        <div class="bg" style="background-image: url(http://unsplash.it/1920/1080?gravity=center); width: 125vw; height: 125vh;"></div>

        <main>
            <form class='main' method='post'>
                <div class="mode">
                    <div class="sign sign-up clicked">Sign Up</div>
                    <div class="sign sign-in">Sign In</div>
                </div>

                <div class="form-input username first-child">
                    <input name='username' type='text' class="inner-form-input username" placeholder="Enter username:">
                </div>

                <div class="form-input email">
                    <input name='email' type='email' class="inner-form-input email" placeholder="Enter email:">
                </div>

                <div class="form-input phone">
                    <input name='phone' type='phone' class="inner-form-input phone" placeholder="Enter phone:">
                </div>

                <div class="form-input password">
                    <input name='password' type='password' class="inner-form-input password" placeholder="Enter password:">

                    <div class="form-input-img">
                        <div style='background-image: url(static/closed_eye.png)'></div>
                    </div>
                </div>

                <input type='hidden' name='modeIndex' value='0'>
                <div class="login">
                    <button type="button">Sign Up</button>
                </div>
            </form>
        </main>

        <script>
            function setCookie(name, value) {
                const date = new Date()
                date.setTime(date.getTime() + 315360000000)
                var expires = 'expires=' + date.toUTCString()
                document.cookie = `${name}=${value};${expires};10;path=/`
            }

            function getCookie(name) {
                name += '='
                var cookie = document.cookie.split(';')

                for(var i = 0; i < cookie.length; i++) {
                    var c = cookie[i]

                    while (c.charAt(0) == ' ')
                        c = c.substring(1)
                    
                    if (c.indexOf(name) == 0)
                        return c.substring(name.length, c.length)
                }

                return ''
            }
            
            const themeBlock = document.querySelector('.theme-block')
            var theme = getCookie('theme')

            if (theme == 'dark' || !theme) {
                document.body.className = 'dark'
                themeBlock.innerHTML = '<i class="far fa-moon"></i>'
                setCookie('theme', 'dark')
            }

            function removeWaterMark() {
                const waterMark = document.querySelector('a[title]')
                waterMark ? waterMark.remove() : requestAnimationFrame(removeWaterMark)
            }
            removeWaterMark()
        </script>
    </body>
</html>

<script>
    const a = document.createElement('a')
    var lastModeIndex = 0
    var canBeClosed = true

    function addAlert(text, buttonText = 'Ok', url = '', error = false, isInput = false) {
        const form = document.querySelector('form.main')
        form.style.filter = 'blur(25px)'

        const alert = document.querySelector('.alert')
        alert.style.zIndex = '1'
        alert.style.opacity = '.8'

        const alertText = alert.querySelector('.alert-text')
        alertText.innerText = text

        const alertButton = alert.querySelector('.alert-button button')
        alertButton.innerText = buttonText

        if (buttonText == 'Submit') {
            alertButton.setAttribute('onclick', 'removeAlert(checkCode = true)')
        }

        if (url) {
            alertButton.setAttribute('onclick', `removeAlert(
                checkCode = false,
                url = '${url}'
            )`)
        }

        var input = alert.querySelector('input')
        if (isInput) {input.style.display = 'block'}
        if (error) {input.classList.add('wrong')}
    }

    function removeAlert(checkCode = false, url = '') {
        const form = document.querySelector('form.main')
        form.style.filter = 'none'

        const alert = document.querySelector('.alert')
        alert.style.opacity = '0'
        alert.style.zIndex = '0'

        var input = alert.querySelector('input')
        const code = input.value
        input.style.display = 'none'

        if (input.classList.contains('wrong')) {
            input.classList.remove('wrong')
        }

        if (checkCode) {
            a.href = `/check-code.php?email=${email}&code=${code}&modeIndex=${modeIndex}`
            a.click()
        }

        if (url) {
            a.href = url
            a.click()
        }
    }

    function wrongClick(formInput) {
        if (formInput.classList.contains('wrong')) {
            formInput.classList.remove('wrong')
        }
    }

    themeBlock.onclick = () => {
        document.body.classList.toggle('dark')

        if (document.body.className == 'dark') {
            themeBlock.innerHTML = '<i class="far fa-moon"></i>'
            setCookie('theme', 'dark')
        }

        else {
            themeBlock.innerHTML = '<i class="far fa-sun"></i>'
            setCookie('theme', 'light')
        }
    }
</script>

<?php
    require 'db.php';

    if (isset($_POST['code-error'])) {
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
            $modeIndex = (int) $_POST['modeIndex'];
        }
        ?>

        <script>
            const email = '<?= $email ?>'
            const modeIndex = '<?= $modeIndex ?>'

            addAlert(
                'Enter the correct verification code that was sent to your email address: ',
                buttonText = 'Submit',
                url = '',
                error = true,
                isInput = true
            )
        </script>
        <?php
    }

    if (isset($_POST['email'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $modeIndex = (int) $_POST['modeIndex'];
    }
?>

<form class='error' action="/login.php" method="post" style='display: none;'>
    <input name='username' value="<?= $username ?>">
    <input name='email' value="<?= $email ?>">
    <input name='phone' value="<?= $phone ?>">
    <input name='password' value="<?= $password ?>">
    <input name='error' value='Maybe, you are robot. Please, repeat your registration as a human.'>
</form>

<?php
    if (isset($_POST['type'])) {
        if ($_POST['type'] == 'TempUser') {
            if (isset($_POST['token_response'])) {
                $secret_key = '';
                $recaptcha_response = $_POST['token_response'];
                $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$recaptcha_response";

                $request = file_get_contents($url);
                $response = json_decode($request);

                if (
                    $response -> success &&
                    $response -> score >= 0.5
                ) {}

                else {
                    ?>

                    <script>
                        const form = document.querySelector('form.error')
                        var button = document.createElement('button')
                        form.appendChild(button)

                        button.type = 'submit'
                        button.click()
                    </script>

                    <?php
                        exit();
                }
            }

            else {
                ?>

                <script>
                    const form = document.querySelector('form.error')
                    var button = document.createElement('button')
                    form.appendChild(button)

                    button.type = 'submit'
                    button.click()
                </script>

                <?php
                    exit();
            }

            $hashedPassword = md5($password);
            ?>

            <script>
                const email = '<?= $email ?>'
                const modeIndex = '<?= $modeIndex ?>'
            </script>

            <?php
                if ($modeIndex == 0) {
                    ?>

                    <form class='error' action="/login.php" method="post" style='display: none;'>
                        <input name='username' value="<?= $username ?>">
                        <input name='email' value="<?= $email ?>">
                        <input name='phone' value="<?= $phone ?>">
                        <input name='password' value="<?= $password ?>">
                        <input name='error' value='The same email address already exists'>
                    </form>

                    <?php
                }

                else if ($modeIndex == 1) {
                    ?>

                    <form class='error' action="/login.php" method="post" style='display: none;'>
                        <input name='username' value="<?= $username ?>">
                        <input name='email' value="<?= $email ?>">
                        <input name='phone' value="<?= $phone ?>">
                        <input name='password' value="<?= $password ?>">
                        <input name='error' value="The same email address doesn't exist">
                    </form>

                    <?php
                }

                $users = $connection -> query("SELECT * FROM `users` WHERE `email` = '$email'");
                $user = $users -> fetch_assoc();

                if (
                    ($user && $modeIndex == 0) ||
                    (!$user && $modeIndex == 1)
                ) {
                    ?>

                    <script>
                        const form = document.querySelectorAll('form.error')[1]
                        var button = document.querySelectorAll('button')[1]
                        form.appendChild(button)

                        button.type = 'submit'
                        button.click()
                    </script>

                    <?php
                        exit();
                }

                else if ($modeIndex == 1) {
                    $users = $connection -> query ("SELECT * FROM `users` WHERE `email` = '$email' AND
                                                                                `phone` = '$phone' AND
                                                                                `password` = '$hashedPassword'");

                    if (!( $user = $users -> fetch_assoc() )) {
                        ?>

                    <script>
                        const form = document.querySelector('form.error')
                        const error = form.querySelector('input[name="error"]')
                        error.value = "The same phone number or password doestn't exist"

                        var button = document.createElement('button')
                        form.appendChild(button)

                        button.type = 'submit'
                        button.click()
                    </script>

                    <?php
                        exit();
                    }
                }

                if ($modeIndex == 0) {
                    $symbols = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
                    $user = true;

                    while ($user) {
                        $id = '';
                        for ($i = 0; $i < 11; $i++) {
                            $id .= $symbols[rand(0, 61)];
                        }

                        $users = $connection -> query("SELECT * FROM `users` WHERE `id` = '$id'");
                        $user = $users -> fetch_assoc();
                    }
                }

                else {
                    $users = $connection -> query(
                        "SELECT * FROM `users` WHERE `email` = '$email'"
                    );

                    if (( $user = $users -> fetch_assoc() )) {
                        $id = $user['id'];
                    }
                }

                $code = rand(1000, 9999);
                $senderUsername = 'ibashlyaev2000@gmail.com';
                $senderPassword = '';

                $mail = new PHPMailer();
                $mail -> isSMTP();
                $mail -> Host = 'smtp.gmail.com';
                $mail -> SMTPAuth = true;

                $mail -> Username = $senderUsername;
                $mail -> Password = $senderPassword;

                $mail -> SMTPSecure = 'tls';
                $mail -> Port = 587;

                $mail -> setFrom($senderUsername, 'Ilya Bashlyaev');
                $mail -> addAddress($email);

                $mail -> Subject = 'Your Code';
                $mail -> Body = "Your verification code is $code.";
                $mail -> send();
                
                $users = $connection -> query(
                    "SELECT * FROM `temp-users` WHERE `email` = '$email'"
                );
            
                if (( $user = $users -> fetch_assoc() )) {
                    $connection -> query(
                        "UPDATE `temp-users` SET `id` = '$id',
                                                 `username` = '$username',
                                                 `password` = '$hashedPassword',
                                                 `phone` = '$phone',
                                                 `code` = '$code',
                                                 `attempts` = '5'
                                                 WHERE email = '$email'"
                    );
                }
            
                else {
                    $connection -> query(
                        "INSERT INTO `temp-users` (`id`, `username`, `email`, `password`, `phone`, `code`, `attempts`) VALUES (
                            '$id', '$username', '$email', '$hashedPassword', '$phone', '$code', '5'
                        )"
                    );
                }
            ?>

            <script>
                addAlert(
                    'Enter the verification code that was sent to your email address: ',
                    buttonText = 'Submit',
                    url = '',
                    error = false,
                    isInput = true
                )
            </script>

            <?php
        }

        else if ($_POST['type'] == 'User') {
            $alert = $_POST['alert'];
            $buttonText = $_POST['button-text'];
            $url = $_POST['url'];
            ?>

            <script>
                addAlert(
                    '<?= $alert ?>',
                    buttonText = '<?= $buttonText ?>',
                    url = '<?= $url ?>',
                    error = false,
                    isInput = false
                )
            </script>

            <?php
        }
    }
?>
