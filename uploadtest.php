<?php
    include "includes/functions.php";
echo 'b';
    if(!empty($_POST['upload']) && !empty($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploaddir = 'assets/user-icons/';
echo 1;
        /* Generates random filename and extension */
        function tempnam_sfx($path, $suffix){
            do {
                $file = $path."/".mt_rand().$suffix;
                $fp = @fopen($file, 'x');
            }
            while(!$fp);

            fclose($fp);
            return $file;
        }
echo 2;
        /* Process image with GD library */
        $verifyimg = getimagesize($_FILES['image']['tmp_name']);
echo 3;
        /* Make sure the MIME type is an image */
        $pattern = "#^(image/)[^\s\n<]+$#i";
echo 4;
        if(!preg_match($pattern, $verifyimg['mime']))
            die("Only image files are allowed!");
echo 5;
        /* Rename both the image and the extension */
        $uploadfile = tempnam_sfx($uploaddir, ".tmp");
echo 6;
        /* Upload the file to a secure directory with the new name and extension */
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {echo 8;
            echo 'Uploaded to ' . htmlspecialchars($uploadfile);
        } else {echo 7;
            die("Image upload failed!");
        }echo 9;
    } else {echo 'x';
        echo '!empty($_POST[\'upload\']) == ' . !empty($_POST['upload']);
        echo "!empty($_FILES['image']) == " . !empty($_FILES['image']);
        echo "($_FILES['image']['error'] == 0) == " . ($_FILES['image']['error'] == 0);
    }echo 'a';
?>
<center>
    Upload test<br>
    <form name="upload" action="/uploadtest/" method="POST" enctype="multipart/form-data">
        Select image to upload: <input type="file" name="image">
        <input type="submit" name="upload" value="upload">
    </form>
</center><?php echo 'd';?>
