<?php
include "../../../../server/sitetools/functions.php";
    
    sec_session_start();
    header("X-Hello-Hacker: Hello! I would love to have a chat with you sometime. You can shoot me an email arinesau@gmail.com. :)");
    header("Content-Type: text/plain");
    
    $json = array();
    $json['success'] = false;
    $language = $_GET['language'];
    
    if(gone($language)) {
        $json['error'] = "Parameter 'language' is not set.";
        die(json_encode($json));
    } else if(isLanguageValid($language)) {
        $json['users'] = getUsersByLanguage($language);
        $json['success'] = true;
        die(json_encode($json));
    } else {
        $json['error'] = "Parameter 'language' is not valid.";
        die(json_encode($json));
    }
?>
