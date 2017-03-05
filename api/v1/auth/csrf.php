<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if(!gone($_REQUEST['sessiondid'])) {
        session_id($_REQUEST['sessionid']);
        session_start();
    } else
        sec_session_start();

    $json['success'] = true;
    $json['sessionid'] = session_id();
    $json['csrf'] = getCSRFToken();

    dump($json);
?>
