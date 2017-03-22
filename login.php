<?php
    include "includes/functions.php";

    $page = '';
    $form = "<form class=\"form-horizontal\" action=\"/login/\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Login</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"username\">Username or Email</label> <div class=\"col-md-5\"> <input id=\"username\" name=\"username\" type=\"text\" placeholder=\"Enter your username or email...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">You can enter either your username or your email address.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"password\">Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter your password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Never tell anyone your password.</span> </div> </div> <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"login\"></label> <div class=\"col-md-4\"> <button id=\"login\" name=\"login\" class=\"btn btn-primary\">Login</button> </div> </div> </fieldset> " . printCSRFToken() . " </form>";
    function error($reason) {
        $form = "<form class=\"form-horizontal\" action=\"/login/\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Login</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"username\">Username or Email</label> <div class=\"col-md-5\"> <input id=\"username\" name=\"username\" type=\"text\" placeholder=\"Enter your username or email...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">You can enter either your username or your email address.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"password\">Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter your password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Never tell anyone your password.</span> </div> </div> <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"login\"></label> <div class=\"col-md-4\"> <button id=\"login\" name=\"login\" class=\"btn btn-primary\">Login</button> </div> </div> </fieldset> " . printCSRFToken() . " </form>";
        return $form . "<br><div class=\"error\">" . $reason . "</div>";
    }

    if(isSignedIn()) { // later redirect to settings.php?return=<url> ...
        $page .= "<div class=\"message\">" . MESSAGE_ALREADY_IN . "</div>";
    } else {
        if(!CAN_LOGIN)
            $page .= error(ERROR_DISABLED_LOGIN);
        if(!SECURE)
            $page .= '<center><div style="color:red;"><b>Warning:</b> Development mode is enabled. Unless you know what you are doing, it may not be safe for you to login.</div></center><br>';

        if($_SERVER['REQUEST_METHOD'] == "POST") {
            if(checkCSRFToken($_POST['csrf'])) {
                if(!(isset($_POST['username']) && isset($_POST['password']))) {
                    error(ERROR_FIELDS_EMPTY);
                } else {
                    $username_or_email = $_POST['username'];
                    $password = $_POST['password'];

                    $status = login($username_or_email, $password);
                    if($status == 0) {
                        header("Location: " . toAbsoluteURL('/'));
                        $page .= "<script>window.location.replace(\"/\");</script></body></html>";
                    } else if($status == 1) {
                        $page .= error(ERROR_PASSWORD_INCORRECT);
                    } else if($status == 2) {
                        $page .= error(ERROR_ACCOUNT_BANNED);
                    } else if($status == 3) {
                        $page .= error(ERROR_PASSWORD_INCORRECT);
                    } else if($status == 4) {
                        $page .= error(ERROR_ACCOUNT_LOCKOUT);
                    } else if($status == 5) { // TODO: move to verify.php
                        $user = getUser($_SESSION['id_unverified']);
                        $id = $user['id'];
                        $token = generateToken($id, 2);
                        $url = toAbsoluteURL('/verify/?token=' . $token . '&id=' . $id);
                        sendEmail($user['email'], 'teendevops', 'info@teendevops.net', 'Welcome to teendevops!', 'To verify that you (<b>' . $user['username'] . '</b>) own this email address, please click the below link:
                            <br><a href="' . $url . '">' . $url . '</a><br><br>If you did not register this account, you can safely ignore this email. Only someone who has access to your email can verify the user.  If you have any questions, please let us know by responding to this email.<br><br>Thanks,<br>    the teendevops team');

                        $page .= error("Another verification email has been dispatched. If you are having a problem, please contact us.");
                    } else {
                        $page .= error(ERROR_UNKNOWN_STATE);
                    }
                }
            } else {
                error("Error: Invalid CSRF token.");
                http_response_code(401);
            }
        } else {
            $page .= $form;
        }
    }
?>

<html>
    <?php include "header.php"; ?>
    <br>

    <body>
        <br>
        <?php echo $page; ?>
        <center>Forgot your password? <a href="/reset/">No problem.</a></center>
        <script>
            window.onload = function() {
                document.getElementById("username").focus();
            };
        </script>
    </body>
</html>
