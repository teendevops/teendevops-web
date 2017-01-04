<html>
    <?php include "header.php"; ?>

    <body>
        <br>
        <?php
            $form = "";
            function error($reason) {
                return $form . "<br><div class=\"error\">" . $reason . "</div>";
            }

            if(isSignedIn()) {
                header("Location: index.php");
                echo "<div class=\"message\">" . MESSAGE_ALREADY_IN . "</div>";
            } else {
                $form = "
                    <form class=\"form-horizontal\" action=\"register.php\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Register Account</legend></center> <!-- Text input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"username\">Username</label> <div class=\"col-md-5\"> <input id=\"username\" name=\"username\" type=\"text\" placeholder=\"Enter a username...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Choose an alphanumeric username of at least 4 characters.</span> </div> </div> <!-- Text input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"email\">Email</label> <div class=\"col-md-5\"> <input id=\"email\" name=\"email\" type=\"text\" placeholder=\"Enter an email...\" class=\"form-control input-md\" required=\"\"" . (!gone($_REQUEST['email']) ? " value=\"" . htmlspecialchars($_REQUEST['email']) . "\"" : "") . "> <span class=\"help-block\">We respect your privacy, and promise never to spam or share your email.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"password\">Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter a password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Please choose a unique password of 6 or more characters.</span> </div> </div> <!-- Password input--> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"confirm_password\">Confirm Password</label> <div class=\"col-md-5\"> <input id=\"confirm_password\" name=\"confirm_password\" type=\"password\" placeholder=\"Enter your password again...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Simply re-enter your password.</span> </div> </div> <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"register\">Ready?</label> <div class=\"col-md-4\"> <button id=\"register\" name=\"register\" class=\"btn btn-primary\">Let's go!</button> </div> </div> </fieldset> </form>

                    <script>
                        document.getElementById(\"email\").value = replaceAll(location.search.split('email=')[1], 'undefined', '');

                        function replaceAll(str, find, replace) {
                            return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
                        }
                    </script>
                ";

                if(!CAN_REGISTER)
                    die(ERROR_DISABLED_REGISTRATION);
                if(!SECURE)
                    echo '<center><div style="color:red;"><b>Warning:</b> Development mode is enabled. Be sure not to reuse a password, because the site may not be secured properly.</div></center><br>';

                if($_SERVER['REQUEST_METHOD'] == "POST") {
                    if(!(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password']))) {
                        echo error(ERROR_FIELDS_EMPTY);
                    } else {
                        $username = $_POST['username'];
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        $confirm_password = $_POST['confirm_password'];

                        if(strlen($password) < 6) {
                            echo error(ERROR_SHORT_PASSWORD);
                        } else {
                            if(strlen($username) < 4 || strlen($username) > 20) {
                                echo error(ERROR_SHORT_USERNAME);
                            } else {
                                if(!isUsernameValid($username)) {
                                    echo error(ERROR_USERNAME_INVALID);
                                } else {
                                    if($password != $confirm_password) {
                                        echo error(ERROR_PASSWORD_MATCH);
                                    } else {
                                        if(!isPasswordSecure($password) || $password == $username) {
                                            echo error(ERROR_PASSWORD_WEAK);
                                        } else {
                                            if(!isEmailValid($email) || strlen($email) > 254) { // max email address length is 254
                                                echo error(ERROR_EMAIL_INVALID);
                                            } else {
                                                if(usernameExists($username)) {
                                                    echo error(ERROR_USERNAME_TAKEN);
                                                } else {
                                                    if(emailExists($email)) {
                                                        echo error(ERROR_EMAIL_TAKEN);
                                                    } else { // TODO: Check for brute forcing
                                                       register($username, $email, $password);
                                                       login($username, $password);
                                                       header("Location: " . toAbsoluteURL('index.php'));
                                                       echo "<script>window.location.replace(\"index.php\");</script>";
                                                       die();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    echo $form;
                }
            }
        ?>
    </body>
</html>
