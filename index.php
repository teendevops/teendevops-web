<html>
    <?php include "header.php"?>
    
    <body>
        <div class="container-full cool-red">
            <div class="row cool-red">
                <div class="col-lg-12 text-center v-center">
                     <?php if(!isSignedIn())
                        echo "<h1>CodeDay-Team</h1><p class=\"lead\">CodeDay-Team is a simple site to connect you with fellow developers.</p> <br class=\"\"> <form class=\"col-lg-12\"> <div class=\"input-group\" style=\"width:340px;text-align:center;margin:0 auto;\"> <input id=\"email\" type=\"text\" class=\"form-control input-lg\" title=\"Don\'t worry. We hate spam, and will not share your email with anyone.\" placeholder=\"Enter your email address...\"> <span class=\"input-group-btn\"><button class=\"btn btn-lg btn-primary\" type=\"button\" onclick=\"openForm()\">OK</button></span> </div> </form> <script> function openForm() { window.location.href = \"register.php?email=\" + document.getElementById(\"email\").value; } </script>";
                        else {
                            $arr = array("Welcome back, " . $_SESSION['username'] . "!", "Aloha!", "Hey!", "Howdy, " . $_SESSION['username'] . "!", "Hello again!", "Sup, " . $_SESSION['username'] . "!");
                            echo "<h1>" . $arr[array_rand($arr)] . "</h1>";
                        }
                     ?>
                     
                     <br>
                </div>
            </div>
        </div>
        
        <?php showSimilar() ?>
    </body>
</html>
