<html>
<?php include "header.php"; ?>
<body>
<br>
<?php
    if(!SECURE)
        echo '<center><div style="color:red;"><b>Warning:</b> Development mode is enabled. Unless you know what you are doing, it may not be safe for you to login.</div></center><br>';

    if(!isSignedIn()) {
        if($_SERVER['REQUEST_METHOD'] == "POST") {
            if(checkCSRFToken($_POST['csrf'])) {
                if(isset($_POST['email'])) { // first time visiting-- send email and stuff
                    if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                        $user = getUserByEmail($email);

                        // TODO: Work on preventing timing attacks
                        if(!gone($user['username'])) {
                            $id = $user['id'];
                            $token = generateResetPasswordToken($id);
                            $url = toAbsoluteURL('/reset/?token=' . $token . '&id=' . $id);

                            sendEmail($email, 'teendevops', 'info@teendevops.net', 'Forgotten Password Reset', 'Someone (hopefully you) requested a new password for the teendevops account <b>' . $user['username'] . '</b><br><br>If you own this account and you requested the reset, please click the link below:
                                <br><a href="' . $url . '">' . $url . '</a><br><br>If you did not request the password reset, you can safely ignore this email. Only someone who has access to your email can reset your password.  If you have any questions, please let us know by responding to this email.<br><br>Thanks,<br>    the teendevops team');
                        } else if(!SECURE)
                            echo 'The user does not exist. ';
                        echo '<center>If a user with that email exists, an email will be sent containing a link to reset your password.<br>If you don\'t receive the email in the next 20 minutes, try <a href="javascript:window.location.reload(true)">requesting the email again</a></center>';
                    } else {
                        echo "<center>Error: Email validation failed.</center>";
                    }
                } else { // second time visiting-- reset password
                    if(!(isset($_POST['id']) && isset($_POST['token']) && isset($_POST['password']))) {
                        echo ERROR_FIELDS_EMPTY;
                    } else {
                        $id = $_POST['id'];
                        $password = $_POST['password'];
                        $token = $_POST['token'];

                        if(validResetPasswordToken($id, $token)) {
                            $ok = false;
                            $message = 'Password has been changed! Click <a href="/login/">here</a> to login.';

                            if(strlen($password) < 6)
                                $message = 'Please choose a password longer than 6 characters.';
                            else if(!isPasswordSecure($password))
                                $message = 'Please choose a stronger password.';
                            else
                                $ok = true;

                            if($ok) {
                                invalidateResetPasswordToken($id);
                                setPassword($id, $password);
                                echo '<center style="color:green">' . $message . '</center>';
                            } else {
                                echo '<center style="color:red">' . $message .'</center>';
                                echo "<form class=\"form-horizontal\" action=\"/reset/\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Reset Password</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"password\">New Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter a new password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Simply enter a new password.</span> </div> </div>  <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"reset\"></label> <div class=\"col-md-4\"> <button id=\"reset\" name=\"reset\" class=\"btn btn-primary\">Reset password</button> </div> </div> </fieldset> " . printCSRFToken();
                                echo '<input type="hidden" id="token" name="token" value="' . $token . '">' . '<input type="hidden" id="id" name="id" value="' . $id . '">' . " </form>";
                            }
                        } else {
                            echo 'Error: Invalid token or id.';
                        }
                    }
                }
            } else {
                error("Error: Invalid CSRF token.");
                http_response_code(401);
            }
        } else { // HTTP GET
            if(isset($_REQUEST['token']) && isset($_REQUEST['id'])) { // second visit after clicking the email link
                if(validResetPasswordToken($_REQUEST['id'], $_REQUEST['token'])) {
                    echo "<form class=\"form-horizontal\" action=\"/reset/\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Reset Password</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"password\">New Password</label> <div class=\"col-md-5\"> <input id=\"password\" name=\"password\" type=\"password\" placeholder=\"Enter a new password...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Simply enter a new password.</span> </div> </div>  <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"reset\"></label> <div class=\"col-md-4\"> <button id=\"reset\" name=\"reset\" class=\"btn btn-primary\">Reset password</button> </div> </div> </fieldset> " . printCSRFToken();
                    echo '<input type="hidden" id="token" name="token" value="' . htmlspecialchars($_REQUEST['token']) . '">' . '<input type="hidden" id="id" name="id" value="' . htmlspecialchars($_REQUEST['id']) . '">' . " </form>";
                } else {
                    echo "Error: Either the password reset link expired, or it never existed.";
                }
            } else { // first visit
                echo "<form class=\"form-horizontal\" action=\"/reset/\" method=\"post\"> <fieldset> <!-- Form Name --> <center><legend>Reset Password</legend></center><!-- Text input--> <div class=\"form-group\"><label class=\"col-md-4 control-label\" for=\"email\">Email</label> <div class=\"col-md-5\"> <input id=\"email\" name=\"email\" type=\"text\" placeholder=\"Enter your email address...\" class=\"form-control input-md\" required=\"\"> <span class=\"help-block\">Simply enter your email address.</span> </div> </div>  <!-- Button --> <div class=\"form-group\"> <label class=\"col-md-4 control-label\" for=\"reset\"></label> <div class=\"col-md-4\"> <button id=\"reset\" name=\"reset\" class=\"btn btn-primary\">Send email</button> </div> </div> </fieldset> " . printCSRFToken() . " </form>";
            }
        }
    } else {
        echo '<center>You\'re already signed in! <a href="/">Go home</a>.</center>';
    }
?>
    </body>
</html>
