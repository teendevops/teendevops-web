<html>
    <?php include "../header.php"; ?>

    <body>
        <center>
            <?php
                if(isSignedIn() && $_SESSION['rank'] != 0) {
                    echo '<h1>Most recent login attempts (100)</h1>';
                    printLoginAttempts(100);
                } else {
                    die('<div class="error">' . ERROR_PERMISSION_DENIED . '</div>');
                }
            ?>
    </center>
    </body>
</html>
