<center><h1>Meet new people...<h1></center>
<?php
    //include "includes/functions.php";
    
    if(isSignedIn()) {
        echo "<div class=\"container\">
  <div class=\"row\">";
  
        $settings = getSettings($_SESSION['id']);
        $array = getUsersByLanguage($settings['languages']);
        
        foreach($array as $usr) {
            echo "          <div class=\"col-sm-3\"><center>
                                <img src=\"assets/user-icons/default.png\" id=\"icon-front\">
                                <h3>" . $settings['languages'] . " Developer</h3>
                            </center></div>";
            echo "          <div class=\"col-sm-3\">
                                <center><h2><a href=\"profile.php?id=" . $usr['id'] . "\">" . htmlspecialchars($usr['username']) . "</a></h2>
                                This is a sample text description of the user. Later, when I join the `settings` and `users` tables, I'll be able to implement this and make it actually display the real description. For now, however, this pointless paragraph will remain riiiiiight here.
                            </div></center>";
        }
        
        echo "</div></div>";
    }
?>
