<?php 
if (!isset($_SERVER['HTTP_REFERER'])) {
    header("Location: home");
    exit();
}
else {
    session_start();

    $_SESSION['username'] = NULL;
    $_SESSION['currentUser'] = NULL;

    header("Location: home");
}
?>  