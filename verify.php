<html>
<?php include "header.php"; ?>
<body>
<br>
<?php
    if(!SECURE)
        echo '<center><div style="color:red;"><b>Warning:</b> Development mode is enabled. Unless you know what you are doing, it may not be safe for you to login.</div></center><br>';

    if(!isSignedIn()) {
        if($_SERVER['REQUEST_METHOD'] == "GET") { // HTTP GET
            if(isset($_REQUEST['token']) && isset($_REQUEST['id'])) { // second visit after clicking the email link
                if(validToken($_REQUEST['id'], 2, $_REQUEST['token'])) {
                    $id = $_GET['id'];
                    $token = $_GET['token'];
                    $user = getUser($id);

                    invalidateToken($id);
                    setVerified($id, true);
                    login($user['username'], $token, true);
                    echo 'Your account has been verified. Ready to see <a href="/profile/">your profile</a>?';
                } else {
                    echo "Error: Either the verification link expired, or it never existed.";
                }
            } else { // first visit
                echo "Error: `token` or `id` not set.";
            }
        }
    } else {
        echo '<center>You\'re already signed in! <a href="/">Go home</a>.</center>';
    }
?>
    </body>
</html>
