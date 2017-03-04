<?php
    include "../../../includes/functions.php";

    header("Content-Type: application/json");

    $json = array();
    $json['success'] = false;

    if(gone($_GET['channel'])) {
        $json['error'] = "Parameter 'channel' is not set.";
        dump($json);
    } else {
        $id = $_GET['channel'];
        $limit = gone($_GET['limit']) ? 100 : $_GET['limit'];
        if($limit > 2500)
        $limit = 2500;
        $arr = getChat($id, $limit, "false");
        $response = array(
            "success"=>true,
            "channel"=>$_GET['channel'],
            "limit"=>$limit,
            "chat"=>$arr
        );

        dump($response);
    }
?>
