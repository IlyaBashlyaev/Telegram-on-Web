<?php
    require 'db.php';

    $useragent = $_SERVER['HTTP_USER_AGENT'];
    $phone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile"));
    $tablet = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "tablet"));

    if (isset($_POST['token_response']) || $phone || $tablet) {
        $secret_key = '6Ldq8ZwbAAAAAOqt5r3tuMeglZP1DpiXnV6nPWll';
        $recaptcha_response = $_POST['token_response'];

        $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$recaptcha_response";
        $request = file_get_contents($url);
        $response = json_decode($request);

        if ((
            $response -> success &&
            $response -> score > 0
        ) || $phone || $tablet) {
            if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.000webhostapp.com')
                $fileURL = '/storage/ssd4/192/20065192/public_html/';
            else
                $fileURL = '';
            $authorId = $_POST['author-id'];

            if (isset($_POST['message-id']))
                $messageId = $_POST['message-id'];
            
            else {
                $maxMessage = $connection ->
                            query('SELECT * FROM `messages` WHERE `message-id` = (SELECT max(`message-id`) FROM `messages`)') ->
                            fetch_assoc();

                if ($maxMessage) {$messageId = (int) $maxMessage['message-id'] + 1;}
                else {$messageId = 0;}
            }
            
            $symbols = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
            $message = true;

            while ($message) {
                $id = '';
                for ($i = 0; $i < 11; $i++) {
                    $id .= $symbols[rand(0, 61)];
                }

                $message = $connection -> query("SELECT * FROM `users` WHERE `id` = '$id'")
                            -> fetch_assoc();
            }

            $file = $_FILES['file'];
            $fileError = $file['error'];

            if (!$fileError) {
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];

                $fileExt = explode('.', $fileName);
                $fileActualExt = strtolower(end($fileExt));

                $imageExt = array('jpg', 'jpeg', 'png', 'svg', 'gif');
                $videoExt = array('mp4', 'mov', 'wmw', 'avi', 'avchd', 'mkv', 'webm');
                $audioExt = array('mp3', 'aac', 'ogg', 'wav');

                if (isset($_POST['edit'])) {
                    $message = $connection -> query("SELECT * FROM `messages` WHERE `message-id` = '$messageId'")
                               -> fetch_assoc();
                    unlink($message['file-name']);
                }
                
                $filePath = $fileURL . "uploads/$id.$fileActualExt";
                move_uploaded_file($fileTmpName, $filePath);

                if (in_array($fileActualExt, $imageExt))
                    $type = 'image';
                else if (in_array($fileActualExt, $videoExt))
                    $type = 'video';
                else if (in_array($fileActualExt, $audioExt))
                    $type = 'audio';
                else
                    $type = 'file';

                if (!isset($_POST['edit'])) {
                    date_default_timezone_set('Europe/Paris');
                    $pubDate = date('Y-m-d H:i:s');

                    $connection -> query(
                        "INSERT INTO `messages` (`message-id`, `id`, `type`, `content`, `file-name`, `pubdate`, `author-id`) VALUES (
                            '$messageId', '$id', '$type', '$fileName', '$filePath', '$pubDate', '$authorId'
                        )"
                    );
                }

                else {
                    if (!isset($filePath))
                        $filePath = 'NULL';

                    $connection -> query(
                        "UPDATE `messages` SET `type` = '$type',
                                               `file-name` = '$fileName',
                                               `file-name` = '$filePath',
                                               `author-id` = '$authorId'
                                               WHERE `message-id` = '$messageId'"
                    );
                }
            }

            else {
                $text = $_POST['text'];

                if (!isset($_POST['edit'])) {
                    date_default_timezone_set('Europe/Paris');
                    $pubDate = date('Y-m-d H:i:s');

                    $connection -> query(
                        "INSERT INTO `messages` (`message-id`, `id`, `type`, `content`, `pubdate`, `author-id`) VALUES (
                            '$messageId', '$id', 'text', '$text', '$pubDate', '$authorId'
                        )"
                    );
                }

                else {
                    $message = $connection -> query("SELECT * FROM `messages` WHERE `message-id` = '$messageId'")
                               -> fetch_assoc();
                    unlink($message['file-name']);

                    if (!isset($filePath))
                        $filePath = 'NULL';
                    
                    $connection -> query(
                        "UPDATE `messages` SET `type` = 'text',
                                               `content` = '$text',
                                               `file-name` = '$filePath'
                                               WHERE `message-id` = '$messageId'"
                    );
                }
            }

            header('Location: /');
        }

        else {
            ?>

            <form action="/" method="post" style="display: none;">
                <input type="hidden" name="author-id" value="<?= $_POST['author-id'] ?>">

                <?php
                    if (isset($_POST['edit'])) {
                        ?>

                        <input type="hidden" name="message-id" value="<?= $_POST['message-id'] ?>">
                        <input type="hidden" name="text" value="">
                        
                        <?php
                    }

                    else {
                        ?>
                        
                        <input type="hidden" name="message-id" value="">
                        <input type="hidden" name="text" value="<?= $_POST['text'] ?>">
                        
                        <?php
                    }
                ?>
                
                <input type="submit" name="return-text">
            </form>

            <script>
                function removeWaterMark() {
                    const waterMark = document.querySelector('a[title]')
                    waterMark ? waterMark.remove() : requestAnimationFrame(removeWaterMark)
                }
                removeWaterMark()
                
                const form = document.querySelector('form')
                form.submit()
            </script>

            <?php
        }
    }
?>