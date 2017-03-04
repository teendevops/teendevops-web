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

    if(!gone($_GET['type'])) {
        $type = $_GET['type'];
        if($type == 'dump') {
            print_r($response);
            die();
        }
    }

    die(json_encode($response));

?>
