<?php

//load requirements
include "../server/sitetools/functions.php";
include "../server/sitetools/strings.php";
        
if(!SECURE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

//Get the request url in an array
$sitePageURLArray = array_values(array_filter(explode('/', $_SERVER['REQUEST_URI'])));

//strip if there is a payload
function payloadStrip($string) {
  return strpos($string, '?') === false;
}

$sitePageURLArray = array_filter($sitePageURLArray, 'payloadStrip');

//sanitize inputs
$_POST = array_map("htmlspecialchars", $_POST);
$_GET = array_map("htmlspecialchars", $_GET);

//are we loading assets or an api?
if(@$sitePageURLArray[0] == 'api' || @$sitePageURLArray[0] == 'assets'){
    //Static content retrieval
    switch (strtolower($sitePageURLArray[0])) {
        case 'api':
            $request = DIR . "api/v1/" . $sitePageURLArray[1];
            if(file_exists($request)){
                require($request);
            } else {
                header('Content-type: application/json');
                echo "404 - Not found";
            }
            exit();            
            #end case
            break;
        
        case 'assets':
            $request = "../server/assets/" . strtolower($sitePageURLArray[1]) . "/" . strtolower($sitePageURLArray[2]);
            if(file_exists($request)){
                if($sitePageURLArray[1] == 'css'){
                    $head = "Content-type: text/css";
                } elseif($sitePageURLArray[1] == 'img'){
                    $head = "Content-type: image";
                } elseif($sitePageURLArray[1] == 'js'){
                    $head = "Content-Type: application/javascript";
                }
                header($head);
                $file = file_get_contents($request);
                echo $file;
            } else {
                header('Content-type: application/json');
                echo "404 - Not found";
            }
            #end case
            break;
        
        default:
            //else load 404 page
    }
} else {
    #load cms pages
    //send headers

    if(EASTER_EGGS) {
        header(base64_decode("WC1IZWxsby1IYWNrZXI6IEhlbGxvISBJIHdvdWxkIGxvdmUgdG8gaGF2ZSBhIGNoYXQgd2l0aCB5b3Ugc29tZXRpbWUuIFlvdSBjYW4gc2hvb3QgbWUgYW4gZW1haWwgYXJpbmVzYXVAZ21haWwuY29tLiA6KQ==")); // if something goes wrong, blame it on Arav :P
        header("X-Easter-Egg: You found an easter egg! id=e4st3r_egg_5");
    }
    header("X-XSS-Protection: 1; mode=block"); // force browser xss protection
    header("X-Frame-Options: SAMEORIGIN"); // prevent clickjacking attacks
    header("X-Content-Type-Options: nosniff"); // prevent mime-type sniffing
    // header("Content-Security-Policy: script-src 'self'"); // commented out because it is annoying to configure and I don't have time right now.
    
    //get the 'header
    require("../server/includes/header.php");
    //check if the page requested is a 'known page

    //find out where we are going
    switch(@strtolower($sitePageURLArray[0])) {
        default:
            require("../server/includes/index.php");
        case 'register':
            require("../server/includes/register.php");
            break;
        case 'profile':
            require("../server/includes/profile.php");
            break;
        case 'login':
            require("../server/includes/login.php");
            break;
        case 'logout':
            require("../server/includes/logout.php");
            break;
        break;
    }
    //\require("../server/includes/footer.php");
}


?>
