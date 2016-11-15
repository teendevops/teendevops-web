<?php
include "../../../../server/sitetools/functions.php";
    
    sec_session_start();
    header("Content-Type: text/plain");
    
    $json = array();
    $json['success'] = false;
    
    if(gone($_GET['channel'])) {
        $json['error'] = "Parameter 'channel' is not set.";
        die(json_encode($json));
    } else {
        $id = $_GET['channel'];
        $max = empty($_GET['max']) ? 100 : $_GET['max'];
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
            } else
                die(json_encode($response));
        }
        
        die(json_encode($response));
    }
?>
