<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(!isSignedIn()) {
            $json['error'] = 'You must be signed in to do that.';
            dump($json);
        } else if(gone($_POST['csrf']) || !checkCSRFToken($_POST['csrf'])) {
            $json['error'] = 'Invalid CSRF Token.';
            dump($json);
        } else if(empty(trim($_POST['message'])) || gone($_POST['message']) || gone($_POST['channel'])) {
            $json['error'] = 'Please fill all POST fields.';
            dump($json);
        } else if(true) {

            sendChat($_SESSION['username'], $_POST['channel'], $_POST['message']);
            $json['success'] = true;
            $json['channel'] = intval($_POST['channel']);
            $json['username'] = $_SESSION['username'];
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
