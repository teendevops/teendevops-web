<?php
include "includes/functions.php";

// LATER: CSRF and POST check.
if(checkCSRFToken($_REQUEST['csrf']))
    logout();
else {
    die("Invalid CSRF token.");
}
?>
<html>
<script>window.location.replace("/");</script>
</html>
