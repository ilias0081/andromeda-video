<?php 
session_start();
/*
[
    {
        "type" : "comment",
        "id": 5
    },
    {
        "type" : "reply",
        "id": 4
    }
]
*/ 

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include 'connect.php';
    
    if ($_POST['for'] == "comment") {
        $db_name = "comments";
        $react_type = "comment";
    }
    else if ($_POST['for'] == "reply") {
        $db_name = "replies";
        $react_type = "reply";
    }

    $result = $connect->query("SELECT * FROM " . $db_name . " WHERE id=" . $_POST['id']);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $other_reactions = json_decode($_SESSION['currentUser']['hearted'], true);
        $hearts = intval($row['hearts']);
        
        if ($_POST['type'] == "heart") {
            $other_reactions[] = array("type" => $react_type, "id" => strval($_POST['id']));
            $hearts += 1;
        }

        else if ($_POST['type'] == "unheart") {
            foreach ($other_reactions as $i => $react) {
                if (strval($react["id"]) == strval($_POST['id']) && strval($react["type"]) == $react_type) {
                    unset($other_reactions[$i]);
                    $hearts -= 1;
                    break;
                }
            }
        }


        $other_reactions = json_encode(array_values($other_reactions));
        if ($connect->query("UPDATE users SET hearted = '$other_reactions' WHERE id=" . $_SESSION['currentUser']['id'])) {
            if ($connect->query("UPDATE " . $db_name . " SET hearts = " . $hearts . " WHERE id = " . $_POST['id'])) {
                $_SESSION['currentUser']['hearted'] = $other_reactions;
                echo json_encode(array("success" => "true", "hearts" => $hearts));
                exit();
            }
        }
    }

} else {
    header("Location: home");
    exit();
}
?>