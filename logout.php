<?php 
include "includes/functions.php";

if(isset($_SERVER['HTTP_REFERER'])){
    header("Location:" . $_SERVER['HTTP_REFERER']);
}
// LATER: CSRF and POST check.
logout();
?>
<html>
<script>window.location.replace("index.php");</script>
</html>
