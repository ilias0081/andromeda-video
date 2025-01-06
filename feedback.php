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
    else if ($connect->query("INSERT INTO feedback(sender_id, text) VALUES ('$user_id', '$text')") === TRUE) {
        echo "Thank you for your feedback!";
    }
    else {
        echo "Feedback failed to submit, please try again later.";
    }
}
else {
    header("Location: index.php");
}
?>