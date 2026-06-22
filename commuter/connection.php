<?php
    $server = "localhost";
    $user = "root";
    $pass = "";
    $db = "comuter_link_nusantara";
    $con = mysqli_connect($server, $user, $pass, $db);

    if (mysqli_connect_errno()) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>