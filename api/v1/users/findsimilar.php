<?php
    include "../../../includes/functions.php";

    checkAPIRate();
    logAPI('users/findsimilar/');

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
