<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;
    $arr = getChannels();
    $response = array(
        "success"=>true,
        "channels"=>$arr
    );

    dump($response);

?>
