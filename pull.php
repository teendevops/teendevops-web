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
<style>
.blinking-cursor {
  -webkit-animation: 1s blink step-end infinite;
  -moz-animation: 1s blink step-end infinite;
  -ms-animation: 1s blink step-end infinite;
  -o-animation: 1s blink step-end infinite;
  animation: 1s blink step-end infinite;
-webkit-transform:scale(1.5, 1.0);
-moz-transform:scale(1.5, 1.0);
-ms-transform:scale(1.5, 1.0);
-o-transform:scale(1.5, 1.0);
transform:scale(1.5,1.0);
}

@keyframes "blink" {
  from, to {
    color: transparent;
  }
  50% {
    color: green;
  }
}

@-moz-keyframes blink {
  from, to {
    color: transparent;
  }
  50% {
    color: green;
  }
}

@-webkit-keyframes "blink" {
  from, to {
    color: transparent;
  }
  50% {
    color: green;
  }
}

@-ms-keyframes "blink" {
  from, to {
    color: transparent;
  }
  50% {
    color: green;
  }
}

@-o-keyframes "blink" {
  from, to {
    color: transparent;
  }
  50% {
    color: green;
  }
}
</style>
    </head>
    <body style="background-color:black;">
        <pre style="color:#0f0;">
$ git pull -v
<?php
echo shell_exec("sudo -u $USER /usr/bin/git pull -v 2>&1");
?>

$ sh syntax.sh
<?php
echo shell_exec("sudo -u $USER /usr/bin/sh syntax.sh 2>&1");
?>

$ <span class="blinking-cursor">|</span></pre>
    </body>
</html>
