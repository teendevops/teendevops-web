<html>
<?php
include "header.php";

// LATER: CSRF and POST check.

logout();
header("Location: " . $_SERVER['HTTP_REFERER']);
?>

<script>window.location.replace("index.php");</script>
</html>
