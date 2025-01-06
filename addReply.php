<?php 
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include 'connect.php';

    $text = mysqli_real_escape_string($connect, $_POST['text_content']);
    $com_id = $_POST['comment_id'];

    if ($connect->query("INSERT INTO replies(owner_id, comment_id, text) VALUES ('{$_SESSION['currentUser']['id']}', '$com_id', '$text')") === TRUE
    &&  $connect->query("UPDATE comments SET replies = replies + 1 WHERE id=" . $com_id) === TRUE) {
        echo json_encode(array(
            "id" => $connect->insert_id,
            "owner_id" => $_SESSION['currentUser']['id'],
            "owner_name" => $_SESSION['currentUser']['username'],
            "text_content" => $text,
            "age" => "0 seconds ago",
            "pfp_path" => $_SESSION['currentUser']['profile_picture_path'],
            "hearts" => 0,
            "hearted" => false,
            "comment_replies" => intval($connect->query("SELECT * FROM comments WHERE id=" . $com_id)->fetch_assoc()['replies'])
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