<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if(!gone($_REQUEST['sessiondid'])) {
        session_id($_REQUEST['sessionid']);
        session_start();
    } else {
        sec_session_start();
    }

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(gone($_POST['csrf']) || gone($_POST['username']) || gone($_POST['password'])) {
            $json['error'] = 'Parameters `csrf`, `username`, and `password` must be set.';
            dump($json);
        } else {
            if(checkCSRFToken($_POST['csrf'])) {
                $status = login($_POST['username'], $_POST['password']);

                if($status == 0) {
                    $json['success'] = isSignedIn();
                    $json['sessionid'] = session_id();
                    $json['csrf'] = getCSRFToken();

                    dump($json);
                } else if($status == 1) {
                    $json['error'] = str_replace("Error: ", "", ERROR_PASSWORD_INCORRECT);
                    dump($json);
                } else if($status == 2){
                    $json['error'] = str_replace("Error: ", "", ERROR_ACCOUNT_BANNED);
                    dump($json);
                } else if($status == 3) {
                    $json['error'] = str_replace("Error: ", "", ERROR_PASSWORD_INCORRECT);
                    dump($json);
                } else if($status == 4) {
                    $json['error'] = str_replace("Error: ", "", ERROR_ACCOUNT_LOCKOUT);
                    dump($json);
                } else {
                    $json['error'] = str_replace("Error: ", "", ERROR_UNKNOWN_STATE);
                    dump($json);
                }
            } else {
                $json['error'] = 'Invalid CSRF Token.';
                dump($json);
            }
        }
    } else {
        $json['error'] = 'Request method must be POST.';
        dump($json);
    }
?>
