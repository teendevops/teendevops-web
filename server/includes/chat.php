<html>
    <?php
        include "header.php"; 

        if(!isSignedIn()) {
            echo "Please login! <script>window.location.replace(\"login.php?return=settings\");</script></html>";
            die();
        }
    ?>
    
    <script src="js/chat.js" crossorigin="anonymous"></script>
    
    <body>
        <br>
        <div class="container">
            <div class="row">
                <div class="row">
                    <div class="col-xs-12">
                        <b>Menu Bar here</b>
                    </div>
                </div>
                <div id="channelWindow" class="col-sm-3">
                    <?php
                        $array = getChannels();
                        
                        foreach($array as $channel) {
                            echo "<a href=\"chat.php?channel=" . $channel['id'] . "\">" . htmlspecialchars($channel['title']) . "</a><br>";
                        }
                    ?>
                </div>
                <div id="chatWindow" class="col-sm-9">
                    Loading...
                </div>
            </div>
            <div class="row">
                <div id="messageWindow" class="col-sm-12 col-md-11">
                    <textarea id="sofar" placeholder="Enter a message..." maxlength="1000"></textarea>
                </div>
                <div id="sendWindow" class="col-md-1">
                    <input type="button" onclick="send()" value="Send">
                </div>
            </div>
        </div>
        <?php
            $there = !gone($_GET['channel']);
        ?>
        <script>
            setInterval(refetch, 500);
            function refetch() {
                var urlx = <?php 
                    if($there) {
                        echo '"htmlchat.php?channel=' . preg_replace("/[^0-9]/", "", $_GET['channel']) .  '"';
                    } else {
                        echo '"htmlchat.php?channel=1"';
                    }
                ?>;
                $.ajax({
                   url:urlx,
                   type:'GET',
                   success: function(data){
                       document.getElementById("chatWindow").innerHTML = data;
                   }
                });
            }
            
            function send() {
                var csrf = "<?php echo getCSRFToken(); ?>";
                var msg = document.getElementById("sofar").value;
                document.getElementById("sofar").value = "";
                $.ajax({
                   url: "/api/v1/chat/send.php",
                   type: "post",
                   data: {
                       csrf: csrf,
                       msg: msg,
                       channel: <?php if($there) echo $_GET['channel']; else echo "1"; ?>
                   },
                   success: function (response) {
                      // you will get response from your php page (what you echo or print)                 
                   }
               });
            }
            
            $("textarea").keydown(function(e){
                if (e.keyCode == 13 && !e.shiftKey) {
                    send();
                    e.preventDefault();
                }
            });
        </script>
    </body>
</html>
