<?php
    require 'db.php';

    if (isset($_POST['token_response'])) {
        $secret_key = '6LceBaAbAAAAAJfSQzrVd5sW_UCFb9ovOM2G3YVK';
        $recaptcha_response = $_POST['token_response'];

        $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$recaptcha_response";
        $request = file_get_contents($url);
        $response = json_decode($request);

        if (
            $response -> success &&
            $response -> score >= 0.5
        ) {
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
                
                $filePath = "uploads/$id.$fileActualExt";
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
                    $connection -> query(
                        "INSERT INTO `messages` (`message-id`, `id`, `type`, `content`, `file-name`, `author-id`) VALUES (
                            '$messageId', '$id', '$type', '$fileName', '$filePath', '$authorId'
                        )"
                    );
                }

                else {
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
                    $connection -> query(
                        "INSERT INTO `messages` (`message-id`, `id`, `type`, `content`, `author-id`) VALUES (
                            '$messageId', '$id', 'text', '$text', '$authorId'
                        )"
                    );
                }

                else {
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
                <?php
                    if (isset($_POST['edit'])) {
                        ?>

                        <input type="hidden" name="author-id" value="<?= $_POST['author-id'] ?>">
                        <input type="hidden" name="message-id" value="<?= $_POST['message-id'] ?>">
                        <input type="hidden" name="text" value="">

                        <?php
                    }

                    else {
                        ?>
                        <input type="hidden" name="text" value="<?= $_POST['text'] ?>">
                        <?php
                    }
                ?>
                <input type="submit" name="return-text">
            </form>

            <script>
                const button = document.querySelector('input[type="submit"]')
                button.click()
            </script>

            <?php
        }
    }
?>