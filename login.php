<html>
    <?php include "header.php"; ?>
    
    <body>
        <br>
        <?php
            if(isSignedIn()) { // later redirect to settings.php?return=<this url!>
                echo "<div class=\"message\">" . MESSAGE_ALREADY_IN . "</div>";
            } else {
                $form = "<form class=\"form-horizontal\" action=\"login.php\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Login</legend></center> <input type=\"hidden\" id=\"csrf\" name=\"csrf\" value=\"" . getCSRFToken() . "\"> <!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"username\">Username or Email</label> <div class=\"col-md-5\"> <input id=\"username\" name=\"username\" type=\"text\" placeholder=\"Enter your username or email...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">You can enter either your username or your email address.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"password\">Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter your password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Never tell anyone your password.</span> </div> </div> <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"login\"></label> <div class=\"col-md-4\"> <button id=\"login\" name=\"login\" class=\"btn btn-primary\">Login</button> </div> </div> </fieldset> </form>";
                
                if($_SERVER['REQUEST_METHOD'] == "POST") {
                    if(isSignedIn())
                        logout();
                    
                    if(/*isset($_POST['csrf']) && $_POST['csrf'] == getCSRFToken()*/ /*Commented out till I find the bug*/true) {
                        if(!(isset($_POST['username']) && isset($_POST['password']))) {
                            echo $form . "<br><div class=\"error\">" . ERROR_FIELDS_EMPTY . "</div>";
                        } else {
                            if(!CAN_LOGIN)
                                die("Sorry, but logging in is temporarily disabled.");
                            
                            $username_or_email = $_POST['username'];
                            $password = $_POST['password'];
                            
                            $status = login($username_or_email, $password);// or die("Fatal Error! Failed to log in for an unknown reason.");
                            
                            if($status == 0) {
                                echo "Success.<script>window.location.replace(\"index.php\");</script></body></html>";
                                die();
                            } else if($status == 1) {
                                echo $form . "<br><div class=\"error\">" . ERROR_PASSWORD_INCORRECT . "</div>";
                            } else if($status == 2){
                                echo $form . "<br><div class=\"error\">" . ERROR_ACCOUNT_BANNED . "</div>";
                            } else if($status == 3) {
                                echo $form . "<br><div class=\"error\">" . ERROR_PASSWORD_INCORRECT . "</div>";
                            } else if($status == 4) {
                                echo $form . "<br><div class=\"error\">" . ERROR_ACCOUNT_LOCKOUT . "</div>";
                            } else {
                                echo $form . "Error: Unknown error.";
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
