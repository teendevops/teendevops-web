<?php
    include "includes/functions.php";

    sec_session_start();

    header("Content-Type: application/json");

    $a_very_painful_death = "Parameter 'channel' is not set.";
    if(gone($_GET['channel'])) // egg: $a_very_painful_death
        /*I'm gunna*/die($a_very_painful_death); // for doing this
    if(gone($_GET['message_id']))
        dump(array('count'=>getChatMessageCount($_GET['channel'])));

    $chat = getChatLatestID($_GET['channel'], $_GET['message_id'] - 1, 500, 'false') or die("Error: Failed to fetch chat");
    dump($chat);
?>
