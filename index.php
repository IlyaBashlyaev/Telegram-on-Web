<?php
    include 'vendor/autoload.php';
    $id = '';
    $google_client = new Google_Client();

    if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.herokuapp.com') {
        $google_client -> setClientId('63638702195-2vbffcs08c5aorccaeligsk9bo51ki9q.apps.googleusercontent.com');
        $google_client -> setClientSecret('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');
        $google_client -> setRedirectUri('https://telegram-on-web.herokuapp.com');
    }

    else {
        $google_client -> setClientId('63638702195-ph4bqevoc6hva1b4lom4fr3r8jmqk13o.apps.googleusercontent.com');
        $google_client -> setClientSecret('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');
        $google_client -> setRedirectUri('http://telegram-web.hopto.org');
    }

    $google_client -> addScope('email');
    $google_client -> addScope('profile');

    require 'db.php';
    $chat = $connection -> query('SELECT * FROM `chats`') -> fetch_assoc();
    $onlineCount = $connection -> query('SELECT * FROM `online-users`') -> num_rows;
    
    if (isset($_GET['code'])) {
        if (isset($_GET['scope'])) {
            $token = $google_client -> fetchAccessTokenWithAuthCode($_GET['code']);
        
            if (!isset($token['error'])) { 
                $google_client -> setAccessToken($token['access_token']);
                $google_service = new Google_Service_Oauth2($google_client);
                $data = $google_service -> userinfo -> get();
            
                if (!empty($data['given_name'])) {
                    $firstName = $data['given_name'];
                } else {
                    $firstName = '';
                }
        
                if (!empty($data['family_name'])) {
                    $lastName = $data['family_name'];
                } else {
                    $lastName = '';
                }
        
                if (!empty($data['email'])) {
                    $email = $data['email'];
                } else {
                    $email = '';
                }
        
                if (!empty($data['picture'])) {
                    $picture = $data['picture'];
                } else {
                    $picture = '';
                }

                $name = $firstName . ' ' . $lastName;
                $user = $connection -> query(
                    "SELECT * FROM `users` WHERE `email` = '$email'"
                ) -> fetch_assoc();

                if (!$user) {
                    $symbols = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
                    $user = true;

                    while ($user) {
                        $id = '';
                        for ($i = 0; $i < 11; $i++) {
                            $id .= $symbols[rand(0, 61)];
                        }

                        $user = $connection -> query("SELECT * FROM `users` WHERE `id` = '$id'")
                                -> fetch_assoc();
                    }

                    $connection -> query(
                        "INSERT INTO `users` (`id`, `first-name`, `last-name`, `email`, `picture`) VALUES (
                            '$id', '$firstName', '$lastName', '$email', '$picture'
                        )"
                    );

                    $members = (int) $chat['members'] + 1;
                    $connection -> query(
                        "UPDATE `chats` SET members = $members
                                            WHERE title = 'Chat'"
                    );

                    $chat = $connection -> query('SELECT * FROM `chats`') -> fetch_assoc();
                }

                else {
                    $id = $user['id'];
                    $connection -> query(
                        "UPDATE `users` SET `first-name` = '$firstName',
                                            `last-name` = '$lastName',
                                            `email` = '$email',
                                            `picture` = '$picture'
                                            WHERE id = '$id'"
                    );
                }

                setcookie('id', $id, time() + 315360000);
            }
            
            header('Location: /');
        }

        else {
            header('Location: /');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">
        <link rel="shortcut icon" href="images/logo.png">

        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600&display=swap"
        rel="stylesheet">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link rel="stylesheet" href="Magnific-Popup/magnific-popup.css">
        <script src="Magnific-Popup/jquery.magnific-popup.min.js"></script>
        <script src="autolink.js"></script>

        <script async src="https://www.googletagmanager.com/gtag/js?id=G-TX9TPBDR41"></script>
        <script>
            window.dataLayer = window.dataLayer || []
            function gtag() {
                dataLayer.push(arguments)
            }
            
            gtag('js', new Date())
            gtag('config', 'G-TX9TPBDR41')
        </script>

        <link rel="stylesheet" href="css/style.css">
        <title>Telegram on Web</title>
    </head>

    <body ontouchstart="touchStart()" ontouchend="touchEnd()" onmouseup="rippleHide()" onclick="rippleHide()" style="background-image: url(<?= $chat['background'] ?>);">
        <div class="pop-up">
            <div class="pop-up-bg" onclick="closeAlert()"></div>

            <div class="pop-up-content">
                <div class="pop-up-title">
                    <i class="fas fa-times" onclick="closeAlert()"></i>
                    <span></span>
                </div>

                <form class="pop-up-options" method="post"></form>
            </div>
        </div>

        <script>
            function closeAlert() {}

            function rippleShow(el) {
                var rippleEl = document.querySelector('span.ripple')

                if (!rippleEl) {var rippleEl = document.createElement('span')}
                else {rippleEl.classList.remove('hide')}

                el.appendChild(rippleEl)
                var max = Math.max(el.offsetWidth, el.offsetHeight)
                rippleEl.style.width = rippleEl.style.height = max + 'px';

                var rect = el.getBoundingClientRect()
                rippleEl.style.left = event.clientX - rect.left - (max / 2) + 'px'
                rippleEl.style.top = event.clientY - rect.top - (max / 2) + 'px'

                rippleEl.classList.add('ripple')
            }

            function rippleHide() {
                const ripples = document.querySelectorAll('.ripple')

                ripples.forEach(ripple => {
                    ripple.classList.add('hide')
                })
            }

            function wrongClick(password) {
                if (password.classList.contains('wrong')) {
                    password.classList.remove('wrong')
                }
            }
        </script>

        <?php
            if (!isset($_COOKIE['user-timezone'])) {
                ?> <script>
                    var timezoneOffset = new Date().getTimezoneOffset()
                    timezoneOffset = timezoneOffset == 0 ? 0 : -timezoneOffset
                    timezoneOffset *= 60
                    console.log(timezoneOffset)

                    $.ajax({
                        url: 'set-timezone.php',
                        type: 'get',
                        data: {timezoneOffset: timezoneOffset},
                        success: res => {
                            location.reload()
                        }
                    })
                </script> <?php
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
                $webView = true;
            else
                $webView = false;

            function showPasswordForm() {
                ?>

                <script>
                    const popUp = document.querySelector('.pop-up'),
                          popUpTitle = document.querySelector('.pop-up-title > span')
                          popUpOptions = document.querySelector('.pop-up-options')

                    popUpTitle.innerText = 'Enter the password:'
                    popUpOptions.action = '/check-password.php'
                    popUpOptions.innerHTML = `<input class="password" name="password" placeholder="Password:" onclick="wrongClick(this)">
<button class="password-button" type="submit">
    <span>Ok</span>
</div>`

                    popUp.classList.add('active')
                </script>

                <?php
                    if (isset($_GET['passwordError'])) {
                        if ($_GET['passwordError'] == 'true') {
                            ?>

                            <script>
                                const password = document.querySelector('.password')
                                password.classList.add('wrong')
                            </script>

                            <?php
                        }
                    }

                    exit();
            }
            if (isset($_COOKIE['verification-key'])) {
                if ($_COOKIE['verification-key'] != 'q9ik15ob9ksgileaccroa5c80scffbv2')
                    showPasswordForm();
            }

            else
                showPasswordForm();
        ?>

        <div class="context-menu" style="transform-origin: top left;">
            <div class="context-block">
                <div class="context-icon">
                    <i class="far fa-check-circle"></i>
                </div>

                <div class="context-text">
                    <span>Select</span>
                </div>
            </div>

            <div class="context-block" onclick="editMessage()">
                <div class="context-icon">
                    <i class="far fa-pencil"></i>
                </div>

                <div class="context-text">
                    <span>Edit</span>
                </div>
            </div>

            <div class="context-block" onclick="deleteMessage()">
                <div class="context-icon">
                    <i class="far fa-trash-alt"></i>
                </div>
                
                <div class="context-text">
                    <span>Delete</span>
                </div>
            </div>
        </div>

        <header>
            <div class="chat-info">
                <div class="chat-logo" style="background-image: url(<?= $chat['logo'] ?>);"></div>

                <div class="chat-title">
                    <div><?= $chat['title'] ?></div>
                    <div><?php 
                        echo $chat['members'] . ' member';
                        if ((int) $chat['members'] != 1) {echo 's';}
                    ?></div>
                </div>
            </div>

            <div class="chat-buttons">
                <div class="search-button">
                    <i class="far fa-search"></i>
                </div>

                <div class="options-button" onclick='showOptions()'>
                    <i class="far fa-ellipsis-v"></i>
                </div>
            </div>
        </header>

        <div class="shadow-block"></div>
        <div class="options">
            <div class="inner-options">
                <div class="options-block">
                    <div class="option" <?php
                        if (!$webView)
                            echo "onclick='newBackgroundImage()'"
                    ?>>
                        <div class="option-icon">
                            <i class="far fa-image"></i>
                        </div>

                        <div class="option-text">
                            <span>Background image</span>
                        </div>
                    </div>

                    <div class="option">
                        <div class="option-icon">
                            <i class="far fa-check-circle"></i>
                        </div>

                        <div class="option-text">
                            <span>Select messages</span>
                        </div>
                    </div>

                    <div class="option" onclick="<?php
                        if (!isset($_COOKIE['id']))
                            echo 'checkUser()';
                        else
                            echo 'signOut()';
                    ?>">
                        <div class="option-icon">
                            <?php
                                if (!isset($_COOKIE['id']))
                                    echo '<i class="fas fa-sign-in-alt"></i>';
                                else
                                    echo '<i class="fas fa-sign-out-alt"></i>';
                            ?>
                        </div>

                        <div class="option-text">
                            <span>
                                <?php
                                    if (!isset($_COOKIE['id']))
                                        echo 'Sign in';
                                    else
                                        echo 'Sign out';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <main>
            <div class="online-block">
                Online:&nbsp;<span><?= $onlineCount ?></span>
            </div>

            <div class="messages">
                <div class="message-alert fixed" style="opacity: 0;">
                    <div class="inner-message-alert"></div>
                </div>

                <?php
                    $monthsArray = array(
                        1 => 'January', 2 => 'February', 3 => 'March',
                        4 => 'April', 5 => 'May', 6 => 'June',
                        7 => 'July', 8 => 'August', 9 => 'September',
                        10 => 'October', 11 => 'November', 12 => 'December'
                    );

                    $messageId = $connection
                                 -> query('SELECT * FROM `messages` WHERE `message-id` = (SELECT max(`message-id`) FROM `messages`)')
                                 -> fetch_assoc();

                    $length = $connection
                              -> query('SELECT COUNT(*) AS `length` FROM `messages`')
                              -> fetch_assoc()['length'];

                    if ($messageId) {
                        $messageId = (int) $messageId['message-id'];
                        $currentPubdate = '';
                        
                        $firstMessageId = $length - 20;
                        if ($firstMessageId < 0)
                            $firstMessageId = 0;

                        $messages = $connection -> query("SELECT * FROM `messages` LIMIT $firstMessageId, 20");
                        $firstMessage = true;
                        
                        while ($message = $messages -> fetch_assoc()) {
                            $content = $message['content'];
                            $type = $message['type'];

                            $currentMessageId = $message['message-id'];
                            $authorId = $message['author-id'];
                            $user = $connection -> query(
                                "SELECT * FROM `users` WHERE `id` = '$authorId'"
                            ) -> fetch_assoc();

                            $firstName = $user['first-name'];
                            $lastName = $user['last-name'];
                            $picture = $user['picture'];

                            if (isset($_COOKIE['user-timezone'])) {
                                $date = new DateTime($message['pubdate'], new DateTimeZone('Europe/Paris'));
                                $date -> setTimezone(new DateTimeZone($_COOKIE['user-timezone']));
                                $pubdate = $date -> format('Y-m-d H:i:s');
                            }

                            else
                                $pubdate = $message['pubdate'];
                            
                            $pubdate = explode(' ', $pubdate);
                            $firstPart = explode('-', $pubdate[0]);
                            $lastPart = explode(':', $pubdate[1]);

                            $year = (int) $firstPart[0];
                            $month = (int) $firstPart[1];
                            $day = $firstPart[2];

                            if ($currentPubdate != $firstPart) {
                                $currentPubdate = $firstPart
                                ?>

                                <div class="message-alert">
                                    <div class="inner-message-alert">
                                        <?= $monthsArray[$month] . " $day, $year" ?>
                                    </div>
                                </div>

                                <?php
                            }

                            $hour = $lastPart[0];
                            $minute = $lastPart[1];

                            if (isset($_COOKIE['id'])) {$id = $_COOKIE['id'];}
                            else {$id = '';}

                            ?>
                                <div class="message-block" style='justify-content: <?php
                                    if ($authorId == $id) {echo 'flex-end';}
                                    else {echo 'flex-start';}
                                ?>;'>
                                    <?php
                                        if ($authorId != $id) {
                                            ?>
                                                <div class="message-picture">
                                                    <div class="inner-message-picture" style="background-image: url(<?= $picture ?>);"></div>
                                                </div>
                                            <?php
                                        }

                                        if ($type == 'text' || $type == 'file') {
                                            if ($authorId != $id) {
                                                ?>
                                                    <div class="message-el">
                                                        <svg style="margin-left: 1px;" xmlns="http://www.w3.org/2000/svg"><defs><filter x="-50%" y="-14.7%" width="200%" height="141.2%" filterUnits="objectBoundingBox" id="a"><feOffset dy="1" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation="1" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0.0621962482 0 0 0 0 0.138574144 0 0 0 0 0.185037364 0 0 0 0.15 0" in="shadowBlurOuter1"></feColorMatrix></filter></defs><g fill="none" fill-rule="evenodd"><path d="M3 17h6V0c-.193 2.84-.876 5.767-2.05 8.782-.904 2.325-2.446 4.485-4.625 6.48A1 1 0 003 17z" fill="#000" filter="url(#a)"></path><path d="M3 17h6V0c-.193 2.84-.876 5.767-2.05 8.782-.904 2.325-2.446 4.485-4.625 6.48A1 1 0 003 17z" fill="#1b1b1b" class="corner"></path></g></svg>
                                                    </div>
                                                <?php
                                            }
                                            ?>
                                            
                                            <div class="message" style="border-radius: <?php
                                                if ($authorId != $id) {echo '10px 10px 10px 0';}
                                                else {echo '10px 10px 0 10px';}
                                            ?>" message-id="<?= $currentMessageId ?>" author-id="<?= $authorId ?>">
                                                <div class="message-author"><?= $firstName . ' ' . $lastName ?></div>

                                                <div class="message-text">
                                                    <?php
                                                        if ($type == 'text')
                                                            echo "<pre>$content</pre>";

                                                        else if ($type == 'file') {
                                                            $filePath = $message['file-name'];
                                                            echo "<a href='$filePath' download='$content'>$content</a>";
                                                        }
                                                    ?>
                                                </div>

                                                <div class="pubdate-block text-pubdate-block">
                                                    <span><?= $hour . ':' . $minute ?></span>
                                                </div>
                                            </div>

                                            <?php
                                            if ($authorId == $id) {
                                                ?>
                                                    <div class="message-el">
                                                        <svg xmlns="http://www.w3.org/2000/svg"><defs><filter x="-50%" y="-14.7%" width="200%" height="141.2%" filterUnits="objectBoundingBox" id="a"><feOffset dy="1" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation="1" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0.0621962482 0 0 0 0 0.138574144 0 0 0 0 0.185037364 0 0 0 0.15 0" in="shadowBlurOuter1"></feColorMatrix></filter></defs><g fill="none" fill-rule="evenodd"><path d="M6 17H0V0c.193 2.84.876 5.767 2.05 8.782.904 2.325 2.446 4.485 4.625 6.48A1 1 0 016 17z" fill="#000" filter="url(#a)" data-darkreader-inline-fill="" style="--darkreader-inline-fill:#e8e6e3;"></path><path d="M6 17H0V0c.193 2.84.876 5.767 2.05 8.782.904 2.325 2.446 4.485 4.625 6.48A1 1 0 016 17z" fill="#1b1b1b"></path></g></svg>
                                                    </div>
                                                <?php
                                            }
                                        }

                                        else if ($type == 'image') {
                                            $filePath = $message['file-name'];
                                            ?>
                                            
                                            <a class="image-link message" href="<?= $filePath ?>" style='<?php
                                                if ($authorId != $id)
                                                    echo 'margin-left:';
                                                else
                                                    echo 'margin-right:';
                                            ?> 10px' message-id="<?= $currentMessageId ?>" author-id="<?= $authorId ?>">
                                                <div class="pubdate-block file-pubdate-block" style="bottom: 14px;"><?= $hour . ':' . $minute ?></div>
                                                <img src="<?= $filePath ?>">
                                            </a>
                                    
                                            <?php
                                        }

                                        else if ($type == 'video') {
                                            $filePath = $message['file-name'];
                                            ?>
                                            
                                            <div class="video-block message" style='<?php
                                                if ($authorId != $id)
                                                    echo 'margin-left:';
                                                else
                                                    echo 'margin-right:';
                                            ?> 10px' message-id="<?= $currentMessageId ?>" author-id="<?= $authorId ?>">
                                                <div class="pubdate-block file-pubdate-block" style="top: 14px;"><?= $hour . ':' . $minute ?></div>
                                                <video controls src="<?= $filePath ?>"></video>
                                            </div>
                                                
                                            <?php
                                        }

                                        else if ($type == 'audio') {
                                            $filePath = $message['file-name'];
                                            ?>

                                            <div class="audio-block message" style="<?php
                                                if ($authorId == $id)
                                                    echo "margin-right: 10px; align-items: flex-end;";
                                                else
                                                    echo "margin-left: 10px; align-items: flex-start;";
                                            ?>" message-id="<?= $currentMessageId ?>" author-id="<?= $authorId ?>">
                                                <audio src="<?= $filePath ?>" controls></audio>
                                                <div class="pubdate-block file-pubdate-block" style="position: relative; <?php
                                                    if ($authorId != $id)
                                                        echo "margin-left: 10px;";   
                                                ?>"><?= $hour . ':' . $minute ?></div>
                                            </div>

                                            <?php
                                        }

                                        if ($authorId == $id) {
                                            ?>
                                                <div class="message-picture">
                                                    <div class="inner-message-picture" style="background-image: url(<?= $picture ?>);"></div>
                                                </div>
                                            <?php
                                        }
                                    ?>
                                </div>
                            <?php
                        }
                    }
                ?>
            </div>

            <script>
                var lastMessageId = parseInt(<?= $messageId ?>) + 1,
                    length = <?= $length ?> - 20
            </script>

            <div class="write-message-block">
                <?php
                    if (isset($_POST['return-text'])) {
                        ?>

                        <input type="hidden" name="edit">
                        <input type="hidden" name="author-id" value="<?= $_POST['author-id'] ?>">
                        <input type="hidden" name="message-id" value="<?= $_POST['message-id'] ?>">

                        <?php
                    }
                ?>

                <div class="border-top"></div>
                <form action='/send-message.php' method='post' class="write-message-content"
                style="border-top: 1px solid transparent;" onclick='checkUser()' enctype="multipart/form-data">
                    <input type="hidden" class='token_response' name='token_response'>
                    <input type="hidden" name='author-id' value='<?php
                        if (isset($_COOKIE['id'])) {echo $_COOKIE['id'];}
                    ?>'>
                    
                    <div class="write-message-input" style="height: 70px;">
                        <div class="emoji-block">
                            <i class="fal fa-smile"></i>
                        </div>

                        <textarea name='text' placeholder='Message' oninput='creatingMessage(this)'<?php
                            if (!isset($_COOKIE['id'])) {echo 'readonly';}
                        ?> style='height: 46.5px;'><?php
                            if (isset($_POST['return-text']) )
                                echo $_POST['text'];
                        ?></textarea>
                        
                        <div class="file-block">
                            <label>
                                <input class="file-message" type="file" name="file" onchange="sendFile(event)">
                            </label>

                            <i class="fas fa-file-upload"></i>
                        </div>
                    </div>

                    <div class="write-message-el" style="height: 70px;">
                        <svg xmlns="http://www.w3.org/2000/svg"><defs><filter x="-50%" y="-14.7%" width="200%" height="141.2%" filterUnits="objectBoundingBox" id="a"><feOffset dy="1" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation="1" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0.0621962482 0 0 0 0 0.138574144 0 0 0 0 0.185037364 0 0 0 0.15 0" in="shadowBlurOuter1"></feColorMatrix></filter></defs><g fill="none" fill-rule="evenodd"><path d="M6 17H0V0c.193 2.84.876 5.767 2.05 8.782.904 2.325 2.446 4.485 4.625 6.48A1 1 0 016 17z" fill="#000" filter="url(#a)" data-darkreader-inline-fill="" style="--darkreader-inline-fill:#e8e6e3;"></path><path d="M6 17H0V0c.193 2.84.876 5.767 2.05 8.782.904 2.325 2.446 4.485 4.625 6.48A1 1 0 016 17z" fill="#1b1b1b"></path></g></svg>
                    </div>
                    
                    <div class="write-message-last">
                        <div class="inner-write-message-last" <?php  
                            if (isset($_POST['return-text'])) {
                                if (!empty($_POST['text']))
                                    echo 'onclick="sendMessage()"><i class="fas fa-paper-plane"></i>';
                                else
                                    echo 'onclick="audioRecorder()"><i class="fas fa-microphone"></i>';
                            }
                            else
                                echo 'onclick="audioRecorder()" ontouchend="audioRecorder()"><i class="fas fa-microphone"></i>';
                            ?>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <input class='chat-background' type="file" accept="image/jpeg, image/png, image/gif" style="display: none;">
        <div class="scroll-down-block">
            <i class="far fa-arrow-down"></i>
        </div>

        <script src="https://www.google.com/recaptcha/api.js?render=6Ldq8ZwbAAAAAN98ra5XtDtLZoUrMg6TJmIHCHMm"></script>
        <script>
            if (Notification.permission == 'default')
                Notification.requestPermission()

            const userAgent = navigator.userAgent
            if (userAgent.includes('Firefox')) {
                const messages = document.querySelector('.messages')
                messages.style.overflowY = 'scroll'
                messages.style.overflowX = 'hidden'
                messages.style.scrollbarColor = '#212121 #323232'

                const textarea = document.querySelector('textarea')
                textarea.style.scrollbarColor = '#252525 #363636'

                const messageAll = messages.querySelectorAll('.message')
                messageAll.forEach(message => message.style.overflow = 'hidden')
            }

            if (!'<?= $webView ?>') {
                const url = localStorage.getItem('url')
                if (url)
                    document.body.style.backgroundImage = `url(${url})`
            }

            grecaptcha.ready(function() {
                grecaptcha.execute('6Ldq8ZwbAAAAAN98ra5XtDtLZoUrMg6TJmIHCHMm', {action: 'submit'}).then(function(token) {
                    var response = document.querySelector('.token_response')
                    response.value = token
                })
            })

            const button = document.createElement('button'),
                  messages = document.querySelector('.messages'),
                  scrollDownBlock = document.querySelector('.scroll-down-block')

            button.style.display = 'none'
            button.type = 'submit'

            var messagesHeight = messages.scrollHeight,
                isOnclick = false, items = false, timer,
                stopRecorder = false, audioFlag = true,
                lastMessage = '', authorId, messageId,
                lastScrollY = messages.scrollTop

            scrollDownBlock.setAttribute('onclick', 'messages.scrollTo(0, messagesHeight)')
            messages.scrollTo(0, messagesHeight)

            function setMessagesStyles() {
                const messages = document.querySelector('.messages'),
                      border_top = document.querySelector('.border-top'),
                      contextMenu = document.querySelector('.context-menu')
                contextMenu.classList.remove('active')
                
                if (messagesHeight < messages.offsetHeight)
                    messages.style.justifyContent = 'flex-end'
                else
                    messages.style.removeProperty('justify-content')

                if (messages.scrollTop + messages.offsetHeight < messagesHeight - 1) {
                    scrollDownBlock.classList.add('active')
                    border_top.style.background = `linear-gradient(
                        to right, rgba(0, 0, 0, 0) 0%, rgba(255, 255, 255, .2) 10%, rgba(255, 255, 255, .2) 90%, rgba(0, 0, 0, 0) 100%
                    )`
                }

                else {
                    scrollDownBlock.classList.remove('active')
                    border_top.style.background = 'transparent'
                }

                const messageAlerts = document.querySelectorAll('.message-alert:not(.fixed)')
                var messageAlertsTop = [], messageAlertsInnerText = []

                for (var i = 0; i < messageAlerts.length; i++) {
                    var messageAlert = messageAlerts[i],
                        {top} = messageAlert.getBoundingClientRect()
                    top -= 100

                    if (top < 0) {
                        messageAlertsInnerText.push(messageAlert.innerText)
                        messageAlertsTop.push(top)
                    }
                }

                var minTop = Math.max.apply(Math, messageAlertsTop),
                    minIndex = messageAlertsTop.indexOf(minTop),
                    innerText = messageAlertsInnerText[minIndex]
                
                const messageAlertFixed = document.querySelector('.message-alert.fixed')
                if (messages.scrollTop >= lastScrollY)
                    messageAlertFixed.style.opacity = '0'
                else
                    messageAlertFixed.style.opacity = '1'
                
                const innerMessageAlertFixed = messageAlertFixed.querySelector('.inner-message-alert')
                innerMessageAlertFixed.innerText = innerText
                lastScrollY = messages.scrollTop
            }
            setMessagesStyles()

            function showLastMessages() {
                const messageAlert = document.querySelector('.message-alert:first-child')

                if (!messages.scrollTop && length > 0) {
                    $.ajax({
                        url: 'show-last-messages.php',
                        type: 'get',
                        data: {length: length},
                        success: content => {
                            const messages = document.querySelector('.messages'),
                                  messageAlertFixed = document.querySelector('.message-alert.fixed'),
                                  messageAlert = document.querySelector('.message-alert:not(.fixed)')
                                  messageAlertFixedContent = messageAlertFixed.outerHTML

                            messageAlertFixed.remove()
                            messageAlert.remove()

                            messages.innerHTML = messageAlertFixedContent + content + messages.innerHTML
                        }
                    })

                    length -= 20
                }

                jQueryCode()
            }

            function setScale() {
                const userAgent = navigator.userAgent,
                      messages = document.querySelector('.messages')

                if (window.innerWidth < 510) {
                    messages.style.zoom = messages.offsetWidth / 5.1 + '%'
                    messages.style.height = '100vh'
                }

                else {
                    messages.style.removeProperty('zoom')
                    messages.style.height = 'calc(100vh - 176px)'
                }
            }
            setScale()

            function surfMessages() {
                if (<?= $length ?>) {
                    $.ajax({
                        url: 'surf-messages.php',
                        type: 'post',
                        data: {lastMessageId: lastMessageId},
                        success: content => {
                            if (content) {
                                const messages = document.querySelector('.messages')
                                messages.innerHTML += content
                                lastMessageId++

                                const messageBlocks = document.querySelectorAll('.message-block'),
                                    lastMessageBlock = messageBlocks[messageBlocks.length - 1],
                                    author = lastMessageBlock.querySelector('.message-author').innerText,
                                    notificationBody = lastMessageBlock.getAttribute('type'),
                                    pictureSrc = lastMessageBlock.querySelector('.picture-source').getAttribute('source')

                                if (Notification.permission == 'granted') {
                                    const notification = new Notification(author, {
                                        body: notificationBody,
                                        icon: pictureSrc
                                    })
                                }
                            }
                        }
                    })
                }
            }
            setInterval(surfMessages, 2000)

            function onlineCounter() {
                $.ajax({
                    url: 'online-counter.php',
                    type: 'post',
                    data: {},
                    success: onlineCount => {
                        const span = document.querySelector('.online-block span')
                        span.innerText = onlineCount
                    }
                })
            }
            setInterval(onlineCounter, 2000)

            function checkUser() {
                var id = '<?php 
                    if (isset($_COOKIE['id'])) {echo $_COOKIE['id'];}
                    else {echo '';}
                ?>'

                if (!id) {
                    const popUp = document.querySelector('.pop-up'),
                          popUpTitle = document.querySelector('.pop-up-title > span'),
                          popUpOptions = document.querySelector('.pop-up-options'),
                          header = document.querySelector('header'),
                          main = document.querySelector('main'),
                          options = document.querySelector('.options'),
                          shadowBlock = document.querySelector('.shadow-block')

                    popUpTitle.innerText = 'Sign in with:'
                    popUpOptions.removeAttribute('action')
                    popUpOptions.innerHTML = `<div class="google-icon-block">
    <div class="google icon" onclick="signIn('google')" onmousedown="rippleShow(this)">
        <i class="fab fa-google"></i>
    </div>
</div>

<div class="facebook-icon-block">
    <div class="facebook-icon" onclick="signIn('facebook')" onmousedown="rippleShow(this)">
        <i class="fab fa-facebook-f"></i>
    </div>
</div>`

                    popUp.classList.add('active')
                    header.classList.add('blurred')
                    main.classList.add('blurred')
                    options.classList.add('blurred')
                    shadowBlock.classList.add('blurred')
                }
            }

            function signIn(key) {
                if (key == 'google')
                    window.location.href = '<?= $google_client -> createAuthUrl() ?>'
            }

            function closeAlert() {
                const popUp = document.querySelector('.pop-up'),
                      header = document.querySelector('header'),
                      main = document.querySelector('main'),
                      options = document.querySelector('.options'),
                      shadowBlock = document.querySelector('.shadow-block')

                popUp.classList.remove('active')
                header.classList.remove('blurred')
                main.classList.remove('blurred')
                options.classList.remove('blurred')
                shadowBlock.classList.remove('blurred')
            }

            function creatingMessage(textarea) {
                var value = textarea.value
                textarea.style.height = '25px'

                const lines = value.split('\n').length,
                      messages = document.querySelector('.messages'),
                      writeMessageBlock = document.querySelector('.write-message-block'),
                      writeMessageInput = writeMessageBlock.querySelector('.write-message-input'),
                      writeMessageEl = writeMessageBlock.querySelector('.write-message-el'),
                      writeMessageLast = writeMessageBlock.querySelector('.write-message-last')

                if (textarea.scrollHeight <= 47) {
                    textarea.style.height = '46.5px'
                    writeMessageInput.style.height = '69.5px'
                    writeMessageEl.style.height = '69.5px'
                    writeMessageLast.style.height = '69.5px'
                }

                if (textarea.scrollHeight > 47 && textarea.scrollHeight <= 417) {
                    textarea.style.height = textarea.scrollHeight - 22.5 + 'px'
                    messages.style.height = `calc(100vh - ${textarea.scrollHeight + 110}px)`
                    writeMessageBlock.style.height = textarea.scrollHeight + 40 + 'px'
                    writeMessageInput.style.height = textarea.scrollHeight + 'px'
                    writeMessageEl.style.height = textarea.scrollHeight + 'px'
                    writeMessageLast.style.height = textarea.scrollHeight + 'px'
                }

                else if (textarea.scrollHeight > 417) {
                    textarea.style.height = '392px'
                    writeMessageInput.style.height = '417px'
                    writeMessageEl.style.height = '417px'
                    writeMessageLast.style.height = '417px'
                }

                const innerWriteMessageLast = document.querySelector('.inner-write-message-last')
                value = value.split(' ').join('')
                value = value.split('\n').join('')

                if (
                    (value || textarea.value != lastMessage) &&
                    innerWriteMessageLast.innerHTML.includes('<i class="fas fa-microphone"></i>')
                ) {
                    innerWriteMessageLast.setAttribute('onclick', 'sendMessage()')
                    innerWriteMessageLast.innerHTML = '<i class="fas fa-paper-plane"></i>'
                }

                else if (
                    (!value || textarea.value == lastMessage) &&
                    innerWriteMessageLast.innerHTML.includes('<i class="fas fa-paper-plane"></i>')
                ) {
                    innerWriteMessageLast.setAttribute('onclick', 'audioRecorder()')
                    innerWriteMessageLast.innerHTML = '<i class="fas fa-microphone"></i>'
                }
            }

            function sendMessage() {
                if (items && audioFlag) {
                    stopRecorder = true
                    audioFlag = false
                }

                else {
                    const writeMessageContent = document.querySelector('.write-message-content')
                    writeMessageContent.submit()
                }
            }

            function audioRecorder() {
                const writeMessageLast = document.querySelector('.inner-write-message-last')
                var device = navigator.mediaDevices.getUserMedia({audio: true})
                items = []

                device.then(stream => {
                    recorder = new MediaRecorder(stream)
                    recorder.ondataavailable = e => {
                        items.push(e.data)

                        if (stopRecorder) {
                            recorder.stop()
                            
                            const fileMessage = document.querySelector('.file-message')
                            var blob = new Blob(items, {type: 'audio/mp3'}),
                                file = new File([blob], "voice-message.mp3", {type:"audio/mp3"}),
                                container = new DataTransfer()
                            
                            container.items.add(file)
                            fileMessage.files = container.files
                            sendMessage()
                        }
                    }

                    recorder.start(100)
                })

                writeMessageLast.setAttribute('onclick', 'sendMessage()')
                writeMessageLast.innerHTML = '<i class="fas fa-paper-plane"></i>'
            }

            async function showOptions() {
                const options = document.querySelector('.options'),
                      optionsBlock = document.querySelector('.options-block')
                
                options.classList.toggle('active')
                optionsBlock.classList.toggle('active')
            }

            function newBackgroundImage() {
                const chatBackground = document.querySelector('.chat-background')
                chatBackground.click()
            }

            function signOut() {
                window.location.href = '/sign-out.php'
            }

            function showContextMenu(event) {
                const contextMenu = document.querySelector('.context-menu')
                event.preventDefault()

                var el = event.target,
                    flag = false

                for (var i = 0; i < 3; i++) {
                    if (el.getAttribute('author-id') == '<?php
                        if (isset($_COOKIE['id']))
                            echo $_COOKIE['id'];
                        else
                            echo " ";
                    ?>') {
                        flag = true
                        authorId = el.getAttribute('author-id')
                        messageId = el.getAttribute('message-id')
                    }
                    el = el.parentNode
                }

                if (flag) {
                    if (event.pageX <= window.innerWidth - 124 && event.pageY <= window.innerHeight - 160)
                        contextMenu.style.transformOrigin = 'top left'
                    else if (event.pageX >= window.innerWidth - 124 && event.pageY <= window.innerHeight - 160)
                        contextMenu.style.transformOrigin = 'top right'
                    else if (event.pageX <= window.innerWidth - 124 && event.pageY >= window.innerHeight - 160)
                        contextMenu.style.transformOrigin = 'bottom left'
                    else if (event.pageX >= window.innerWidth - 124 && event.pageY >= window.innerHeight - 160)
                        contextMenu.style.transformOrigin = 'bottom right'

                    if (event.pageX <= window.innerWidth - 124)
                        contextMenu.style.left = event.pageX + 'px'
                    else
                        contextMenu.style.left = event.pageX - 124 + 'px'
                    
                    if (event.pageY <= window.innerHeight - 160)
                        contextMenu.style.top = event.pageY + 'px'
                    else
                        contextMenu.style.top = event.pageY - 160 + 'px'
                    
                    contextMenu.classList.add('active')
                }

                else
                    contextMenu.classList.remove('active')
            }

            function touchStart() {
                timer = setTimeout(() => $('#body').trigger('touchstart'), 400)
            }

            function touchEnd() {
                if (timer) {
                    clearTimeout(timer)
                    timer = null
                }
            }

            function editMessage() {
                const form = document.querySelector('.write-message-content')
                
                form.innerHTML = `<input type="hidden" name="edit" value="edit">
<input type="hidden" name="author-id" value="${authorId}">
<input type="hidden" name="message-id" value="${messageId}">` + form.innerHTML

                const textarea = form.querySelector('textarea')
                try {
                    const lastMessage = document.querySelector(`.message[message-id='${messageId}'] .message-text pre`).innerHTML
                    textarea.value = lastMessage
                } catch {
                    textarea.value = ''
                }
                
                creatingMessage(textarea)
            }

            function deleteMessage() {
                $.ajax({
                    url: 'delete-message.php',
                    type: 'post',
                    data: {
                        authorId: authorId,
                        messageId: messageId
                    },
                    success: res => {
                        const message = document.querySelector(`.message[message-id='${messageId}']`).parentNode
                        message.remove()
                    }
                })
                setMessagesStyles()
            }

            function jQueryCode() {
                $('.image-link').magnificPopup({
                    type: 'image',
                    mainClass: 'mfp-with-zoom',

                    zoom: {
                        enabled: true,
                        duration: 250,
                        easing: 'linear',
                    }
                })

                jQuery(function($) {
                    $('body').autolink();
                })
            }
            jQueryCode()

            const chatBackground = document.querySelector('.chat-background')
            chatBackground.addEventListener('change', event => {
                var file = event.target.files[0],
                    reader = new FileReader(),
                    img = document.createElement('img')

                reader.readAsDataURL(file)
                reader.addEventListener('load', event => {
                    localStorage.setItem('url', event.target.result)
                })

                location.reload()
            }, false)

            function sendFile(event) {
                var file = event.target.files[0]
                
                if (file.size <= 52428800)
                    sendMessage()
                else
                    alert('The file can\'t be bigger than 50 MB')
            }

            document.addEventListener('keydown', e => {
                if (e.code == 'Escape') {
                    const popUp = document.querySelector('.pop-up')
                    if (popUp.classList.contains('active'))
                        closeAlert()
                }

                else if (e.ctrlKey && e.code == 'Enter') {
                    const innerWriteMessageLast = document.querySelector('.inner-write-message-last')
                    var value = document.querySelector('textarea').value
                    
                    value = value.split(' ').join('')
                    value = value.split('\n').join('')

                    if (value)
                        sendMessage()
                }
            })

            messages.addEventListener('scroll', () => {
                setMessagesStyles(), showLastMessages()
            })

            window.addEventListener('resize', () => {
                setMessagesStyles(); setScale()
            })

            window.addEventListener("contextmenu", showContextMenu)
            window.addEventListener("click", () => {
                const contextMenu = document.querySelector('.context-menu'),
                      options = document.querySelector('.options'),
                      optionsBlock = options.querySelector('.options-block')

                var el = event.target, isOptionsButton = false
                contextMenu.classList.remove('active')
                
                for (var i = 0; i < 2; i++) {
                    if (el) {
                        if (el.className == 'options-button')
                            isOptionsButton = true
                        el = el.parentNode
                    }
                }

                if (!isOptionsButton && options.classList.contains('active')) {
                    options.classList.remove('active')
                    optionsBlock.classList.remove('active')
                }
            })
        </script>
    </body>
</html>