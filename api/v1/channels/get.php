<?php
    include "../../../includes/functions.php";
    
    sec_session_start();
    header("X-Hello-Hacker: Hello! I would love to have a chat with you sometime. You can shoot me an email arinesau@gmail.com. :)");
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
