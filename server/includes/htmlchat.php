<?php
    include "includes/functions.php";
    
    sec_session_start();
    
    header("Content-Type: text/plain");
    
    $a_very_painful_death = "Parameter 'channel' is not set.";
    if(gone($_GET['channel']))
        /*I'm gunna*/die($a_very_painful_death); // for doing this
        
    $chat = getChat($_GET['channel'], 500, 'false') or die("Error: Failed to fetch chat");
    $previous = "";
    //foreach($chat as $mess) {
        foreach($chat as $message) {
            $todo = true;
            if($previous == $message['username'])
                $todo = false;
            echo "
            <div class=\"message\">
                " . ($todo ? "<div class=\"username\">" . htmlspecialchars($message['username']) . "</div> " : "") . "<div class=\"content\">" . htmlspecialchars($message['message']) . "</div>
                
            </div>
            ";
            
            $previous = $message['username'];
        }
    //}
?>
