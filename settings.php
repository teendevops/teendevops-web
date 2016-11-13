<html>
    <?php
        include "header.php";
        
        if(!isSignedIn()) {
            echo "Please login! <script>window.location.replace(\"login.php?return=settings\");</script>";
            die();
        }
    ?>
    
    <body>
        <?php 
            $csrftoken = getCSRFToken();
            
            if($_SERVER['REQUEST_METHOD'] == "POST") {
                if(isset($_POST['csrf']) && $_POST['csrf'] == $csrftoken) {
                    if(!isLanguageValid($_POST['languages']))
                        $_POST['languages'] = "None";
                    setSettings($_SESSION['id'], $_POST['description'], $_POST['languages'], $_POST['locationx']);
                } else {
                    echo "Error: Invalid CSRF token.";
                    http_response_code(401);
                }
            }
        ?>
        <br>
        <form class="form-horizontal" action="settings.php" method="post">
            <fieldset>

            <!-- Form Name -->
            <center><legend>Settings</legend></center>
            <input type="hidden" id="csrf" name="csrf" value="<?php echo $csrftoken; ?>">
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
