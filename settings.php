<html>
    <?php
        include "header.php";

        if(!isSignedIn()) {
            echo "Please login! <script>window.location.replace(\"/login/?return=settings\");</script>";
            die();
        }
    ?>

    <body><br>
        <?php
            if($_SERVER['REQUEST_METHOD'] == "POST") {
                if(isset($_POST['csrf']) && checkCSRFToken($_POST['csrf'])) {
                    $success = false;
                    if(!isLanguageValid($_POST['languages']))
                        $_POST['languages'] = "None";
                    setSettings($_SESSION['id'], $_POST['description'], $_POST['languages'], $_POST['locationx']);
                    $success = true;

                    if(!empty($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $uploaddir = 'assets/user-icons/';

                        /* Generates random filename and extension */
                        function tempnam_sfx($path, $suffix) {
                            do {
                                $file = $path . "/" . preg_replace("/[^A-Za-z0-9 ]/", '', bin2hex(openssl_random_pseudo_bytes(16))) . $suffix; // doesn't need to be cryptographically secure
                                //unlink(getcwd() . $file) or die("Failed to delete old");
                                $fp = @fopen($file, 'x');
                            }
                            while(!$fp);

                            fclose($fp);
                            return $file;
                        }

                        /* Process image with GD library */
                        $verifyimg = getimagesize($_FILES['image']['tmp_name']);

                        /* Make sure the MIME type is an image */
                        $pattern = "#^(image/)[^\s\n<]+$#i";

                        if(!preg_match($pattern, $verifyimg['mime'])) {
                            echo '  <div class="alert alert-danger">
                                      <strong>Uh oh!</strong> That doesn\'t quite look like an image file!.
                                    </div>';
                            return;
                        }

                        /* Rename both the image and the extension */
                        $uploadfile = tempnam_sfx($uploaddir, ".png");

                        /* Resize the file */
                        $fn = $_FILES['image']['tmp_name'];
                        $size = getimagesize($fn);
                        $ratio = $size[0] / $size[1]; // width/height

                        if( $ratio > 1) {
                            $width = 300;
                            $height = 300/$ratio;
                        } else {
                            $width = 300 * $ratio;
                            $height = 300;
                        }

                        $src = imagecreatefromstring(file_get_contents($fn));
                        $dst = imagecreatetruecolor($width, $height);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
                        imagedestroy($src);
                        imagepng($dst, $_FILES['image']['tmp_name']); // adjust format as needed
                        imagedestroy($dst);

                        /* Upload the file to a secure directory with the new name and extension */
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
                            setProfileImage($_SESSION['id'], '/' . $uploadfile);
                            $success = true;
                        } else {
                            echo '<div class="alert alert-danger">
                                    <strong>Whoops!</strong> Something went wrong.
                                  </div>';
                        }
                    }

                    if($success)
                        echo '<div class="alert alert-success">
                              <strong>Success!</strong> Your <a href="/profile/' . htmlspecialchars($_SESSION['username']) . '/">profile</a> has been updated.
                            </div>';
                } else {
                    echo '  <div class="alert alert-danger">
                              <strong>Error:</strong> Invalid CSRF token.
                            </div>';
                }
            }
        ?>
        <form class="form-horizontal" enctype="multipart/form-data" action="/settings/" method="post">
            <fieldset>

            <!-- Form Name -->
            <center><legend>Settings</legend></center>
            <?php echo printCSRFToken(); ?>
            <!-- Textarea -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="description">Description</label>
              <div class="col-md-4">
                <textarea class="form-control" id="description" name="description"><?php echo $_SESSION['html_description']; ?></textarea>
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="locationx">Location</label>
              <div class="col-md-5">
              <input id="locationx" name="locationx" type="text" placeholder="Los Angeles, CA" class="form-control input-md" value="<?php echo $_SESSION['html_location']; ?>">
              <span class="help-block">Don't be too specific.</span>
              </div>
            </div>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="languages">Favorite Language</label>
              <div class="col-md-5">
                <select id="languages" name="languages" class="form-control">
                  <option value="<?php echo $_SESSION['html_languages']; ?>" selected><b><?php echo $_SESSION['html_languages']; ?></b></option>
                  <option value="None">None</option>
                  <option value="Java">Java</option>
                  <option value="C">C</option>
                  <option value="C++">C++</option>
                  <option value="C#">C#</option>
                  <option value="Python">Python</option>
                  <option value="PHP">PHP</option>
                  <option value="NodeJS">NodeJS</option>
                  <option value="Scratch">Scratch</option>
                  <option value="Visual Basic">Visual Basic</option>
                  <option value="HTML/CSS/JS">HTML/CSS/JS</option>
                  <option value="Assembly">Assembly</option>
                  <option value="Ruby">Ruby</option>
                  <option value="Perl">Perl</option>
                  <option value="Pascal">Pascal</option>
                  <option value="Scala">Scala</option>
                  <option value="Lua">Lua</option>
                  <option value="D">D</option>
                  <option value="Swift">Swift</option>
                  <option value="Objective-C">Objective-C</option>
                  <option value="R">R</option>
                  <option value="Go">Go</option>
                  <option value="SQL">SQL</option>
                </select>
              </div>
            </div>

            <!-- File input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="image">User Icon</label>
              <div class="col-md-5">
              <input id="image" type="file" class="file" name="image">
              <span class="help-block">Image will be resized to 300x300</span>
              </div>
            </div>

            <!-- Button -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="save">Done?</label>
              <div class="col-md-4">
                <button id="save" name="save" class="btn btn-primary">Save</button>
              </div>
            </div>
            </fieldset>
        </form>
    </body>
</html>
