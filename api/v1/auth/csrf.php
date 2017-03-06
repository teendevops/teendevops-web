<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    $json['success'] = true;
    $json['sessionid'] = session_id();
    $json['csrf'] = getCSRFToken();

    dump($json);
?>
