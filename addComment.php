<?php 
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include 'connect.php';

    $text = mysqli_real_escape_string($connect, $_POST['text']);
    $vid_id = $_POST['video_id'];

    if ($connect->query("INSERT INTO comments(owner_id, video_id, text) VALUES ('{$_SESSION['currentUser']['id']}', '$vid_id', '$text')") === TRUE) {
        echo json_encode(array(
            "id" => $connect->insert_id,
            "owner_id" => $_SESSION['currentUser']['id'],
            "owner_name" => $_SESSION['currentUser']['username'],
            "text_content" => $text,
            "age" => "0 seconds ago",
            "pfp_path" => $_SESSION['currentUser']['profile_picture_path'],
            "hearts" => 0,
            "hearted" => false,
            "replies" => 0
        ));
    }
    else {
        echo json_encode(array());
    }

} else {
    header("Location: home");
    exit();
}
?>