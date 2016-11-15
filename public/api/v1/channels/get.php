<?php
include "../../../../server/sitetools/functions.php";
    
    sec_session_start();
    header("Content-Type: text/plain");
    
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
        } else
            die(json_encode($response));
    }
    
    die(json_encode($response));
    
?>
