<html>
    <?php include "../header.php"; ?>

    <body>
        <center>
            <?php
                if(isSignedIn() && $_SESSION['rank'] != 0) {
                    $limit = 100;
                    if(!gone($_REQUEST['limit']) && $_REQUEST['limit'] < 2000)
                        $limit = $_REQUEST['limit'];
                    $gone = gone($_REQUEST['filter']);
                    echo '<h1>Most recent login attempts (' . htmlspecialchars($limit) . ')</h1><a href="/admin/attempts/?' . (!$gone ? '">Order by date' : 'filter=true">Filter by failures') . '</a>';
                    printLoginAttempts($limit, ($gone ? '' : " WHERE `success`='false'")); // I don't feel like this is secure. TODO: pentest
                    echo '<div class="error">This page is incomplete, and may not be stable! If you are experiencing any issues, please contact the devs.</div>';
                } else {
                    die('<div class="error">' . ERROR_PERMISSION_DENIED . '</div>');
                }
            ?>
    </center>
    </body>
</html>
