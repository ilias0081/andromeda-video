<?php

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header("Location: index.php");
    exit();
}
else {
    $host = "";
    $user="";
    $pass = "";
    $db = "";
    $connect = new mysqli($host, $user, $pass, $db);

    if ($connect->connect_error) {
        echo "Failed to connect to database.".$connect->connect_error;
    }
}
?> 