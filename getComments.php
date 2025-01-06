<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include 'connect.php';


    $result = $connect->query("SELECT * FROM comments WHERE video_id=" . intval($_POST['video_id']) . " ORDER BY rand() LIMIT " . intval($_POST['count']));
    $data = array();

    if ($result->num_rows > 0) {
        while (TRUE) { 
            $row = $result->fetch_assoc();
            if ($row == null) {
                break;
            }
            $owner = $connect->query("SELECT * FROM users WHERE id=" . $row['owner_id'])->fetch_assoc();

            if (isset($owner)) {
                $comment_date = new DateTime($row['date'], new DateTimeZone('UTC'));
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $age = $now->diff($comment_date);
                if ($age->y > 0) 
                    $date = ($age->y == 1) ? $age->y . " year" : $age->y . " years";
                else if ($age->m > 0) 
                    $date = ($age->m == 1) ? $age->m . " month" : $age->m . " months";
                else if ($age->d > 0) 
                    $date = ($age->d == 1) ? $age->d . " day" : $age->d . " days";
                else if ($age->h > 0) 
                    $date = ($age->h == 1) ? $age->h . " hour" : $age->h . " hours";
                else if ($age->i > 0) 
                    $date = ($age->i == 1) ? $age->i . " minute" : $age->i . " minutes";
                else if ($age->s >= 0)
                    $date = $age->s . " seconds";
                $date = $date . " ago";

                $hearted = false;

                if (isset($_SESSION['currentUser'])) {
                    $other_reactions = json_decode($_SESSION['currentUser']['hearted'], true);

                    foreach ($other_reactions as $react) {
                        if ($react['id'] == strval($row['id']) && $react['type'] == "comment") 
                            $hearted = true;
                    }
                }

                $data[] = array(
                    "id" => $row['id'],
                    "owner_id" => $row['owner_id'],
                    "owner_name" => $owner['username'],
                    "text_content" => $row['text'],
                    "age" => $date,
                    "pfp_path" => $owner['profile_picture_path'],
                    "hearts" => intval($row['hearts']),
                    "hearted" => $hearted,
                    "replies" => intval($row['replies'])
                );
            }
        }
    }
    echo json_encode($data);
} else {
    header("Location: home");
    exit();
}
// str_contains($_SESSION['currentUser']['other_reactions'], '{"type":"comment","id":' . strval($row['id']) . '}')
?>