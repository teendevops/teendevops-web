<?php
    include "../../../includes/functions.php";

    checkAPIRate();
    logAPI('channels/get/');

    $json = array();
    $json['success'] = false;
    $arr = getChannels();
    $response = array(
        "success"=>true,
        "channels"=>$arr
    );

    dump($response);

?>
