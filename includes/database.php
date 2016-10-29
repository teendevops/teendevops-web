<?php

include "config.php";

function getConnection() {
    return new mysqli(HOST, USER, PASSWORD, DATABASE);
}
?>
