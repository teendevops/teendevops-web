<html>
    <?php include "../header.php"; ?>

    <body>
        <center>
            <?php
                if(isSignedIn() && $_SESSION['rank'] != 0) {
                    // TODO: make page
                    echo '<div class="error">This page is incomplete, and may not be stable! If you are experiencing any issues, please contact the devs.</div>';
                } else {
                    die('<div class="error">' . ERROR_PERMISSION_DENIED . '</div>');
                }
            ?>
    </center>
    </body>
</html>
