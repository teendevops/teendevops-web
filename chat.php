<html>
    <?php
        include "header.php";

        if(!isSignedIn()) {
            echo "Please login! <script>window.location.replace(\"/login/?return=chat\");</script></html>";
            die();
        }
    ?>

    <script src="js/chat.js" crossorigin="anonymous"></script>

    <body>
        <br><br>
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
                            echo "<a href=\"/chat/" . htmlspecialchars($channel['id']) . "/\">#" . htmlspecialchars($channel['title']) . "</a><br>";
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

            $urlx = '/htmlchat/?channel=1';
            if($there)
                $urlx = '/htmlchat/?channel=' . preg_replace("/[^0-9]/", "", $_GET['channel']) .  '"';
        ?>
        <script>
            var last = 0;
            var lastuser = "";

            $.ajax({
                url:"<?php echo $urlx; ?>",
                type:'GET',
                success: function(data) {
                    last = data.count - 25; // load last 25 messages...
                }
            });

            setInterval(refetch, 400);
            function refetch() {
                var urlx = "<?php echo $urlx; ?>&message_id=" + (last + 1);
                $.ajax({
                    url:urlx,
                    type:'GET',
                    success: function(data){
                        var wind = document.getElementById("chatWindow");

                        for (var i = 0; i < data.length; i++) {
                            var msg = data[i];
                            if(msg.message_id > last)
                                last = msg.message_id;

                            var builder = "";
                            if(lastuser != msg.username) {
                                lastuser = msg.username;
                                builder = builder + "<br><b>" + escapeHTML(msg.username) + "</b><br>";
                            }

                            builder = builder + escapeHTML(msg.message) + "<br>";
                            wind.innerHTML = wind.innerHTML + builder;
                            wind.scrollTop = wind.scrollHeight;
                        }
                   }
                });
            }



            function escapeHTML(unsafe) {
                return unsafe
                     .replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
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
                       message: msg,
                       channel: <?php if($there) echo preg_replace("/[^0-9]/", "", $_GET['channel']); else echo "1"; ?>
                   },
                   success: function (response) {
                      // handle the response here
                      var object = JSON.parse(response);
                      if(!object.success)
                        alert("Message failed to send: " + object.error);
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
