<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    $result = ob_get_clean();
    $json['result'] = $result;

    $json['success'] = true;
    $json['sessionid'] = session_id();
    $json['csrf'] = getCSRFToken();

    dump($json);
?>
