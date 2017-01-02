<?php
/* This file automatically pulls the latest version of the site from the git 
 * repository. It requires that $USER be set up to pull the repo and that the
 * following line exist in /etc/sudoers:
 * www-data    ALL=($USER) NOPASSWD: /usr/bin/git pull -v
 * Where $USER is replaced with the specified value of $USER. Note that the
 * parentheses must be there and are not for annotation purposes.
 * */
$USER='duncan';
?>
<html>
    <head>
        <title>git pull</title>
    </head>
    <body style="background-color:black;">
        <pre style="color:#0f0;">
$ git pull -v
<?php
echo shell_exec("sudo -u $USER /usr/bin/git pull -v 2>&1");
?>
        </pre>
    </body>
</html>
