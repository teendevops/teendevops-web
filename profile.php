<html>
    <?php include "header.php"; ?>

    <body>
        <br>
        <div class="container">
            <?php
                $edit = "<a href=\"/settings/\"><div class=\"edit\"><span class=\"glyphicon glyphicon-pencil\"></span> edit</div></a>";

                if(!empty($_GET['username']))
                    $user = getUserByName(explode(".", $_GET['username'], 2)[0]);
                else if(!gone($_GET['id']))
                    $user = getUser(explode(".", $_GET['id'], 2)[0]);
                else if(isSignedIn())
                    $user = getUserByName($_SESSION['username']);
                else
                    echo "Invalid parameters. Redirecting to index...<script>window.location.replace(\"/\");</script>";
            ?>
            <div class="row">
                <div class="col-sm-3">
                    <center>
                        <img src="/assets/user-icons/default.png">
                        <h1><a href="/profile/<?php echo htmlspecialchars($user['username']); ?>/"><?php echo htmlspecialchars($user['username']); ?></a></h1>
                        <h2><?php echo htmlspecialchars(NAME) . " " . $user['rank_html'] ?></h2>
                        <h2 class="lang"><?php
                            $href = ($user['id'] == $_SESSION['id']);
                            $print = isSignedIn() && ($_SESSION['rank'] != 0 || $href);

                            $language = $user['languages'];
                            if(empty($language) || $language == "None") {
                                echo "Language Unspecified";
                            } else {
                                echo htmlspecialchars($language) . " Developer";
                            }

                            echo "</h2>" . ($print ? $edit : '');

                            $ctf = ctfBalance($user['id']);
                            if(isset($ctf) && $ctf != 0)
                                echo '<h2>' . ($href ? '<a href="/ctf/">' : '') . $ctf . ' ctf tokens' . ($href ? '</a>' : '') . '</h2>';
                        ?>
                    </center>
                </div>
                <div class="col-sm-9">
                    <center>
                        <h2>Description</h2>
                        <div class="aboutme">
                            <?php
                                echo "<h3>" . htmlspecialchars($user['description']) . "</h3>";
                                if($print)
                                    echo $edit;
                            ?>
                        </div>
                    </center>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                </div>
            </div>
        </div>
    </body>
</html>
