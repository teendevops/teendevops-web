<?php
    include "../../../includes/functions.php";

    sec_session_start();
    header("Content-Type: text/plain");

    $json = array();
    $json['success'] = false;

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(!isSignedIn()) {
            $json['error'] = 'You must be signed in to do that.';
            die(json_encode($json));
        } else if(gone($_POST['csrf']) || checkCSRFToken($_POST['csrf'])) {
            $json['error'] = 'Invalid CSRF Token.';
            die(json_encode($json));
        } else if(gone($_POST['msg']) || gone($_POST['channel'])) {
            $json['error'] = 'Please fill all POST fields.';
            die(json_encode($json));
        } else if(/*isChannelExistant($_POST['channel'])*/true) {
            sendChat($_SESSION['username'], $_POST['channel'], $_POST['msg']);
            $json['success'] = true;
            die(json_encode($json));
        } else {
            $json['error'] = 'That channel does not exist.';
            die(json_encode($json));
        }
    } else {
        $json['error'] = 'Request method must be POST.';
        die(json_encode($json));
    }
?>
