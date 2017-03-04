<?php
include "../../../includes/functions.php";

    header("Content-Type: text/plain");

    $json = array();
    $json['success'] = false;
    $language = $_GET['language'];

    if(gone($language)) {
        $json['error'] = "Parameter 'language' is not set.";
        dump($json);
    } else if(isLanguageValid($language)) {
        $json['users'] = getUsersByLanguage($language);
        $json['success'] = true;
        dump($json);
    } else {
        $json['error'] = "Parameter 'language' is not valid.";
        dump($json);
    }
?>
