<?php 
/*
[
    {
        "type" : "like",
        "id" : 5
    },
    {
        "type" : "dislike",
        "id" : 4
    }
]
*/

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include 'connect.php';

    $vid_id = $_POST['video_id'];
    $sql = "SELECT likes, dislikes FROM videos WHERE id='$vid_id'";
    $result = $connect->query($sql);

    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
        $reactions = json_decode($_SESSION['currentUser']['reactions'], true);

        if ($_POST["type"] == "like") {
            $changed_type = FALSE;

            foreach ($reactions as $i => $react) {
                if ($react["id"] == strval($vid_id) && $react["type"] == "dislike") {
                    $reactions[$i]["type"] = "like";
                    $changed_type = TRUE;
                    $video["dislikes"] -= 1;
                    break;
                }
            }

            if (!$changed_type) 
                $reactions[] = array("type" => "like", "id" => strval($vid_id));
            $video["likes"] += 1;
        }

        else if ($_POST["type"] == "unlike") {
            foreach ($reactions as $i => $react) {
                if ($react["id"] == strval($vid_id) && $react["type"] == "like") {
                    unset($reactions[$i]);
                    $video["likes"] -= 1;
                    break;
                }
            }
        }

        else if ($_POST["type"] == "dislike") {
            $changed_type = FALSE;

            foreach ($reactions as $i => $react) {
                if ($react["id"] == strval($vid_id) && $react["type"] == "like") {
                    $reactions[$i]["type"] = "dislike";
                    $changed_type = TRUE;
                    $video["likes"] -= 1;
                    break;
                }
            }

            if (!$changed_type) 
                $reactions[] = array("type" => "dislike", "id" => strval($vid_id));
            $video["dislikes"] += 1;
        }

        else if ($_POST["type"] == "undislike") {
            foreach ($reactions as $i => $react) {
                if ($react["id"] == strval($vid_id) && $react["type"] == "dislike") {
                    unset($reactions[$i]);
                    $video["dislikes"] -= 1;
                    break;
                }
            }
        }
        
        $reactions = json_encode(array_values($reactions));
        if ($connect->query("UPDATE users SET reactions = '$reactions' WHERE id=" . $_SESSION['currentUser']['id'])) {
            if ($connect->query("UPDATE videos SET likes = " . $video["likes"] . ", dislikes = " . $video["dislikes"] . " WHERE id = " . $vid_id)) {
                $_SESSION['currentUser']['reactions'] = $reactions;
                echo json_encode(array("success" => "true", "likes" => $video["likes"], "dislikes" => $video["dislikes"]));
                exit();
            }
        }
    }
    echo json_encode(array("success" => "true"));
}
else {
    header("Location: home");
    exit();
}
?> 