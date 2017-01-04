<html>
    <?php include "header.php"; ?>

    <body>
        <br>
        <?php
            $form = "<form class=\"form-horizontal\" action=\"login.php\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Login</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"username\">Username or Email</label> <div class=\"col-md-5\"> <input id=\"username\" name=\"username\" type=\"text\" placeholder=\"Enter your username or email...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">You can enter either your username or your email address.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"password\">Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter your password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Never tell anyone your password.</span> </div> </div> <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"login\"></label> <div class=\"col-md-4\"> <button id=\"login\" name=\"login\" class=\"btn btn-primary\">Login</button> </div> </div> </fieldset> " . printCSRFToken() . " </form>";
            function error($reason) {
                return $form . "<br><div class=\"error\">" . $reason . "</div>";
            }

            if(isSignedIn()) { // later redirect to settings.php?return=<url> ...
                echo "<div class=\"message\">" . MESSAGE_ALREADY_IN . "</div>";
            } else {
                if(!CAN_LOGIN)
                    die(ERROR_DISABLED_LOGIN);
                if(!SECURE)
                    echo '<center><div style="color:red;"><b>Warning:</b> Development mode is enabled. Unless you know what you are doing, it may not be safe for you to login.</div></center><br>';

                if($_SERVER['REQUEST_METHOD'] == "POST") {
                    if(checkCSRFToken($_POST['csrf'])) {
                        if(!(isset($_POST['username']) && isset($_POST['password']))) {
                            echo $form . "<br><div class=\"error\">" . ERROR_FIELDS_EMPTY . "</div>";
                        } else {
                            $username_or_email = $_POST['username'];
                            $password = $_POST['password'];

                            $status = login($username_or_email, $password);

                            if($status == 0) {
                                header("Location: " . toAbsoluteURL('index.php'));
                                echo "<script>window.location.replace(\"index.php\");</script></body></html>";
                                die();
                            } else if($status == 1) {
                                echo error(ERROR_PASSWORD_INCORRECT);
                            } else if($status == 2){
                                echo error(ERROR_ACCOUNT_BANNED);
                            } else if($status == 3) {
                                echo error(ERROR_PASSWORD_INCORRECT);
                            } else if($status == 4) {
                                echo error(ERROR_ACCOUNT_LOCKOUT);
                            } else {
                                echo error(ERROR_UNKNOWN_STATE);
                            }
                        }
                    } else {
                        echo "Error: Invalid CSRF token.";
                        http_response_code(401);
                    }
                } else {
                    echo $form;
                }
            }
        ?>
    </body>
</html>
