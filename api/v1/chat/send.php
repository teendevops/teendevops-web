<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(!isSignedIn() && (gone($_POST['sessionid']) || !session_valid_id($_POST['sessionid']))) {
            $json['error'] = 'You must be signed in to do that.';
            dump($json);
        } else if(gone($_POST['csrf']) || !checkCSRFToken($_POST['csrf'])) {
            $json['error'] = 'Invalid CSRF Token.';
            dump($json);
        } else if(empty(trim($_POST['message'])) || gone($_POST['message']) || gone($_POST['channel'])) {
            $json['error'] = 'Please fill all POST fields.';
            dump($json);
        } else if(/*isChannelExistant($_POST['channel'])*/true) {
            if(!isSignedin && !gone($_POST['sessionid'])) {
                session_id($_POST['sessionid']);
                session_start();

                if(!isSignedIn()) {
                    $json['error'] = 'Invalid `sessionid` supplied.';
                    dump($json);
                }
            }

            sendChat($_SESSION['username'], $_POST['channel'], $_POST['message']);
            $json['success'] = true;
            $json['username'] = $_POST['username'];
            $json['channel'] = $_POST['channel'];
            $json['message'] = $_POST['message'];
            dump($json);
        } else {
            $json['error'] = 'That channel does not exist.';
            dump($json);
        }
    } else {
        $json['error'] = 'Request method must be POST.';
        dump($json);
    }
?>
