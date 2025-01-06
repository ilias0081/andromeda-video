<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'connect.php';

    $amount = $_POST['amount'];
    $sql = "SELECT * FROM videos ORDER BY rand() LIMIT " . intval($amount);
    $result = $connect->query($sql);

    if ($result->num_rows > 0) {
        $data = array();

        while (TRUE) {
            $row = $result->fetch_assoc(); 
            if ($row == null) {
                break;
            }

            $user_sql = "SELECT * FROM users WHERE id=" . intval($row['owner_id']);
            $user_result = $connect->query($user_sql);

            if ($user_result->num_rows > 0) {
                $user_row = $user_result->fetch_assoc();

                $video_date = new DateTime($row['date'], new DateTimeZone('UTC'));
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $age = $now->diff($video_date);
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

                $data[] = array(
                    "video_id" => $row['id'],
                    "thumbnail_path" => $row['thumbnail_path'],
                    "video_name" => $row['name'],
                    "owner_id" => $row['owner_id'],
                    "owner_name" => $user_row['username'],
                    "profile_picture_path" => $user_row['profile_picture_path'],
                    "age" => $date . " ago",
                    "views" => $row['views']
                );
            }
        }
        
        echo json_encode($data);
    }

}
else {
    header("Location: index.php");
}
?> 