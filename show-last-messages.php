<?php
    require 'db.php';
    $length = (int) $_GET['length'];

    if ($length < 20) {
        $startPoint = 0;
        $queryLength = $length;
    }
    
    else {
        $startPoint = $length;
        $queryLength = 21;
    }

    $messages = $connection -> query("SELECT * FROM `messages` LIMIT $startPoint, $queryLength");
    $i = 0;

    while ($message = $messages -> fetch_assoc()) {
        $messageAlertDelete = false;

        $monthsArray = array(
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December'
        );

        $currentPubdate = '';
        $content = $message['content'];
        $type = $message['type'];

        $messageId = $message['message-id'];
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

        if (isset($prevMessage)) {
            $prevPubDate = explode(' ', $prevMessage);
            $prevFirstPart = explode('-', $prevPubDate[0]);

            if ($prevFirstPart != $firstPart) {
                $currentPubdate = $firstPart;
                $startPoint = 1;
                ?>

                <div class="message-alert">
                    <div class="inner-message-alert">
                        <?= $monthsArray[$month] . " $day, $year" ?>
                    </div>
                </div>

                <?php
            }
        }

        else if ($startPoint == 0) {
            $currentPubdate = $firstPart;
            $startPoint = 1;
            ?>

            <div class="message-alert">
                <div class="inner-message-alert">
                    <?= $monthsArray[$month] . " $day, $year" ?>
                </div>
            </div>

            <?php
        }

        $prevMessage = $message['pubdate'];
        $hour = $lastPart[0];
        $minute = $lastPart[1];

        if (isset($_COOKIE['id'])) {$id = $_COOKIE['id'];}
        else {$id = '';}

        if ($i != 20) {
            ?>
            
            <div class="message-block" style="justify-content: <?php
                if ($authorId == $id) {echo 'flex-end';}
                else {echo 'flex-start';}
            ?>" type="<?php
                if ($type == 'text') {
                    $compressedContent = mb_substr($content, 0, 50, 'utf-8');
                    echo $compressedContent;
                    
                    if ($content != $compressedContent)
                        echo ' ...';
                }
                else if ($type == 'file') echo 'File Message';
                else if ($type == 'image') echo 'Photo Message';
                else if ($type == 'video') echo 'Video Message';
                else if ($type == 'audio') echo 'Audio Message';
            ?>">
                <?php
                    if ($authorId != $id) {
                        ?>
                            <div class="message-picture">
                                <div class="picture-source" style="display: none;" source="<?= $picture ?>"></div>
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
                        ?>" message-id="<?= $messageId ?>" author-id="<?= $authorId ?>">
                            <div class="message-author"><?= $firstName . ' ' . $lastName ?></div>

                            <div class="message-text">
                                <?php
                                    if ($type == 'text')
                                        echo "<pre>$content</pre>";

                                    else if ($type == 'file') {
                                        if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.000webhostapp.com')
                                            $filePath = 'uploads/' . explode('/', $message['file-name'])[7];
                                        else
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
                        if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.000webhostapp.com')
                            $filePath = 'uploads/' . explode('/', $message['file-name'])[7];
                        else
                            $filePath = $message['file-name'];
                        ?>
                        
                        <a class="image-link message" href="<?= $filePath ?>" style='<?php
                            if ($authorId != $id)
                                echo 'margin-left:';
                            else
                                echo 'margin-right:';
                        ?> 10px' message-id="<?= $messageId ?>" author-id="<?= $authorId ?>">
                            <div class="pubdate-block file-pubdate-block" style="bottom: 14px;"><?= $hour . ':' . $minute ?></div>
                            <img src="<?= $filePath ?>">
                        </a>
                
                        <?php
                    }

                    else if ($type == 'video') {
                        if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.000webhostapp.com')
                            $filePath = 'uploads/' . explode('/', $message['file-name'])[7];
                        else
                            $filePath = $message['file-name'];
                        ?>
                        
                        <div class="video-block message" style='<?php
                            if ($authorId != $id)
                                echo 'margin-left:';
                            else
                                echo 'margin-right:';
                        ?> 10px' message-id="<?= $messageId ?>" author-id="<?= $authorId ?>">
                            <div class="pubdate-block file-pubdate-block" style="top: 14px;"><?= $hour . ':' . $minute ?></div>
                            <video controls src="<?= $filePath ?>"></video>
                        </div>
                            
                        <?php
                    }

                    else if ($type == 'audio') {
                        if ($_SERVER['HTTP_HOST'] == 'telegram-on-web.000webhostapp.com')
                            $filePath = 'uploads/' . explode('/', $message['file-name'])[7];
                        else
                            $filePath = $message['file-name'];
                        ?>

                        <div class="audio-block message" style="<?php
                            if ($authorId == $id)
                                echo "margin-right: 10px; align-items: flex-end;";
                            else
                                echo "margin-left: 10px; align-items: flex-start;";
                        ?>" message-id="<?= $messageId ?>" author-id="<?= $authorId ?>">
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
                                <div class="picture-source" style="display: none;" source="<?= $picture ?>"></div>
                                <div class="inner-message-picture" style="background-image: url(<?= $picture ?>);"></div>
                            </div>
                        <?php
                    }
                ?>
            </div>
            
            <?php
        }
        
        $i++;
    }
?>