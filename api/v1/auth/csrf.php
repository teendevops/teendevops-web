<?php
    include "../../../includes/functions.php";

    checkAPIRate();
    logAPI('auth/csrf/');

    $json = array();
    $json['success'] = false;

    $json['success'] = true;
    $json['sessionid'] = session_id();
    $json['csrf'] = getCSRFToken();

    dump($json);
?>
