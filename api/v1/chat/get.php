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
        /*  $tops = (isSignedIn() ? 1000 : 100);
            if($max > $tops) */
        $max = 1000;
        $arr = getChat($id, $max, "false");
        $response = array(
            "success"=>true,
            "channel"=>$_GET['channel'],
            "max"=>$max,
            "chat"=>$arr
        );

        if(!gone($_GET['type'])) {
            $type = $_GET['type'];
            if($type == 'dump') {
                print_r($response);
                die();
            }
        }

        die(json_encode($response));
    }
?>
