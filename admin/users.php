<html>
    <?php include "../header.php"; ?>

    <body>
        <center>
            <?php
                if(isSignedIn() && $_SESSION['rank'] != 0) {
                    $gone = gone($_REQUEST['filter']);
                    echo '<h1>Users (500)</h1>';
                    $users = getUsers();
                    echo '<div class="container"><table class="table table-striped"><thead><tr>
                            <th>Username</th>
                            <th>Language</th>
                            <th>Location</th>
                            <th>Description</th>
                          </tr></thead><tbody>';
                    foreach($users as $user) {
                        echo '<tr>
                        <td><a href="/profile.php?id=' . $user['id'] . '">' . htmlspecialchars($user['username']) . '</td>
                        <td>' . htmlspecialchars($user['language']) . '
                        <td>' . htmlspecialchars($user['location']) . '
                        <td>' . htmlspecialchars($user['description']) . '
                        </tr>';
                    }
                    echo '</tbody></table></div>';
                    echo '<div class="error">This page is incomplete, and may not be stable! If you are experiencing any issues,</div>';
                } else {
                    die('<div class="error">' . ERROR_PERMISSION_DENIED . '</div>');
                }
            ?>
    </center>
    </body>
</html>
