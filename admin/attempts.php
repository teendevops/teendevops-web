<html>
    <?php include "../header.php"; ?>

    <body>
        <center>
            <?php
                if(isSignedIn() && $_SESSION['rank'] != 0) {
                    $gone = gone($_REQUEST['filter']);
                    echo '<h1>Most recent login attempts (100)</h1><a href="/admin/attempts.php?filter=true">Filter by failures</a>';
                    printLoginAttempts(100, ($gone ? '' : ' WHERE `success`=\'false\'')); // I don't feel like this is secure. TODO: pentest
                    echo '<div class="error">This page is incomplete, and may not be stable! If you are experiencing any issues,</div>';
                } else {
                    die('<div class="error">' . ERROR_PERMISSION_DENIED . '</div>');
                }
            ?>
    </center>
    </body>
</html>
