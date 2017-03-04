<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if(gone($_GET['channel'])) {
        $json['error'] = "Parameter 'channel' is not set.";
        die(json_encode($json));
    } else {
        $id = $_GET['channel'];
        $max = gone($_GET['max']) ? 100 : $_GET['max'];
        if($max > 2500)
        $max = 2500;
        $arr = getChat($id, $max, "false");
        $response = array(
            "success"=>true,
            "channel"=>$_GET['channel'],
            "max"=>$max,
            "chat"=>$arr
        );

        dump($response);
    }
?>
