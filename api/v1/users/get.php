<?php
    include "../../../includes/functions.php";

    header("Content-Type: text/plain");

    $json = array();
    $json['success'] = false;

    if($_SERVER['REQUEST_METHOD'] == "GET") {
        $user = array();
        if(!gone($_REQUEST['id']))
            $user = getUser($_REQUEST['id']);
        else if(!gone($_REQUEST['username']))
            $user = getUserByName($_REQUEST['username']);
        else {
            $json['error'] = 'Either parameter `id` or `username` must be set.';
            dump($json);
        }

        if(!gone($user['banned']) && !$user['banned']) {
            $json['success'] = true;
            $json['user'] = $user;
            dump($json);
        } else {
            $json['error'] = 'That user does not exist.';
            dump($json);
        }
    } else {
        $json['error'] = 'Request method must be GET.';
        dump($json);
    }
?>
