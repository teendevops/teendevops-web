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
                    <input type="button" onclick="send()" value="Send">
                </div>
            </div>
        </div>
        <?php
            $there = !gone($_GET['channel']);

            $urlx = '/htmlchat/?channel=1';
            if($there)
                $urlx = '/htmlchat/?channel=' . preg_replace("/[^0-9]/", "", $_GET['channel']);
        ?>
        <script>
            var swearFilter = true; // set this to false to disable filtering

            var last = 0;
            var lastuser = "";
            var lastmessage = "";
            var first = true;
            var replace = {};

            $.ajax({
                url:"<?php echo $urlx; ?>",
                type:'GET',
                success: function(data) {
                    last = data.count - 35; // load last 35 messages...
                }
            });

            setInterval(refetch, 400);
            function refetch() {
                var urlx = "<?php echo $urlx; ?>&message_id=" + (last + 1);
                var scroll = false; // auto scroll on message?
                $.ajax({
                    url:urlx,
                    type:'GET',
                    success: function(data){
                        var wind = document.getElementById("chatWindow");

                        for (var i = 0; i < data.length; i++) {
                            scroll = true; // enable scrolling
                            var msg = data[i];
                            if(lastmessage != msg.message) {
                                if(msg.message_id > last)
                                    last = msg.message_id;

                                var builder = "";
                                if(lastuser != msg.username) {
                                    lastuser = msg.username;
                                    builder = builder + "<br><b><a style=\"color:black\" href=\"/profile/" + escapeHTML(msg.username) + "\">" + escapeHTML(msg.username) + "</a></b><br>";
                                }
                                builder = builder + "<div id=\"id_" + msg.message_id + "\">" + escapeHTML(msg.message.replace("[shrug]", "\u00AF\\_(\u30C4)_\/\u00AF").replace("[lenny]", "( ͡° ͜ʖ ͡°)").replace("[rip]", "ಠ_ಠ")) + "</div>";

                                wind.innerHTML = wind.innerHTML + builder;

                                if(msg.message.trim().toLowerCase().startsWith("/clear")) { // temporary clearing feature
                                    wind.innerHTML = "";
                                    lastuser = "";
                                }

                                if(first)
                                    wind.scrollTop = wind.scrollHeight;

                            } else
                                scroll = false;

                            lastmessage = msg.message;
                        }

                        if(data.length != 0 && swearFilter) {
                            var html = wind.innerHTML;
                            for (var key in replace)
                                html = html.replace(key, replace[key]);
                            wind.innerHTML = html;
                        }
                        if(scroll)
                            scrollSmoothToBottom("chatWindow");
                        if(first)
                            first = false;
                   }
                });
            }

            function scrollSmoothToBottom(id) {
                var div = document.getElementById(id);
                $('#' + id).animate({
                    scrollTop: div.scrollHeight - div.clientHeight
                }, 500);
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
                if(document.getElementById("sofar").value != "") {
                    var csrf = "<?php echo getCSRFToken(); ?>";
                    var msg = document.getElementById("sofar").value; // store the form's value
                    document.getElementById("sofar").value = ""; // clear the form
                    $.ajax({
                        url: "/api/v1/chat/send.php",
                        type: "post",
                        data: {
                            csrf: csrf,
                            message: msg,
                            channel: <?php if($there) echo preg_replace("/[^0-9]/", "", $_GET['channel']); else echo "1"; ?>
                        },
                       success: function (response) {
                            //var object = JSON.parse(response);
                        }
                    });
                }
            }

            $("textarea").keydown(function(e){
                if (e.keyCode == 13 && !e.shiftKey) {
                    send();
                    e.preventDefault();
                }
            });

            /* swear word filter */
            $.ajax({
                url:"/assets/blacklist.txt",
                type:'GET',
                success: function(data) {
                    data = data.replace(/[a-zA-Z]/g,function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);});
                    var lines = data.split("\n");
                    for(var i = 0, len = lines.length; i < len; i++) {
                        var split = lines[i].split("=");
                        replace[split[0]] = split[1];
                    }
                }
            });
        </script>
    </body>
</html>
