<?php 
session_start();

if (!isset($_SERVER['HTTP_REFERER'])) {
    header("Location: index.php");
}

else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    include 'connect.php';

    $text = mysqli_real_escape_string($connect, $_POST['text']);;
    $user_id = $_SESSION['currentUser']['id'];
    if ($text == "") {
        echo "Feedback failed to submit, please enter text into the box.";
    }
    else if ($connect->query("UPDATE users SET about = '$text' WHERE id=" . $user_id) === TRUE) {
        $_SESSION['currentUser']['about'] = $text;
        echo "About section successfully edited!";
    }
    else {
        echo "About section failed to be edited, please try again later.";
    }
}
else {
    header("Location: index.php");
}
?>