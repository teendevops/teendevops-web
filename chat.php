<html>
    <?php
        include "header.php";

        if(!isSignedIn()) {
            echo "Please login! <script>window.location.replace(\"/login/?return=chat\");</script></html>";
            die();
        }
    ?>

    <body>
        <br>
        <div class="container">
            <div class="row">
                <div class="row">
                    <div id="" class="col-xs-12">
                        <b>Menu Bar here</b>
                    </div>
                </div>
                <div id="channelWindow" class="col-sm-3">
                    <?php
                        $array = getChannels();

                        foreach($array as $channel)
                        echo "<a " . ($channel['id'] != $_GET['channel'] ? 'style="color:#008000"' : 'style="font-weight: bold;color:#006400"') . " href=\"/chat/" . htmlspecialchars($channel['id']) . "/\">#" . htmlspecialchars($channel['title']) . "</a><br>";
                    ?>
                </div>
                <div id="chatWindow" class="col-sm-9" style="font-family: opensansemoji;"></div>
            </div>
            <div class="row">
                <div id="messageWindow" class="col-sm-12 col-md-11">
                    <textarea id="sofar" placeholder="Enter a message..." maxlength="1000"></textarea>
                </div>
                <div id="sendWindow" class="col-md-1">
                    <input type="button" id="send" value="Send">
                </div>
            </div>
        </div>
        <script src="/js/chat.php?<?php echo "v=" . rand(1, 100000) ?>" crossorigin="anonymous"></script>
        
        <?php include "footer.php"; ?>
    </body>
</html>
