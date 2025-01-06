<?php 
session_start();

if (isset($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);
    $_SESSION['searchQuery'] = $query;

    include 'connect.php';

    $result = $connect->query("SELECT *, MATCH(name) AGAINST('$query') AS relevance 
                            FROM videos 
                            WHERE MATCH(name) AGAINST('$query') 
                            ORDER BY relevance DESC");

    $videos = "";

    while ($row = $result->fetch_assoc()) {
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

        $owner = $connect->query("SELECT * FROM users WHERE id=" . $row['owner_id'])->fetch_assoc();

        $videos .=
        '<div class="vid" id="vid' . $row['id']. '">
            <img class="thumbnail" src="' . $row['thumbnail_path'] . '" onclick="vid(' . $row['id'] . ')" width="160px" height="90px">
            <div class="vid_info">
                <h class="video_name">' . $row['name'] . '</h>
                <br>
                <h class="views_and_age">' . $row['views'] . ' views | ' . $date . ' ago</h>
                <div class="video_owner" onclick="user(' . $owner['id'] . ')">
                    <img class="owner_pfp" src="' . $owner['profile_picture_path'] . '" width="20px" height="20px">
                    <h class="owner_name">' . $owner['username'] . '</h>
                </div>
            </div>
        </div>';
    }

    $result = $connect->query("SELECT *, MATCH(username) AGAINST('$query') AS relevance 
                            FROM users 
                            WHERE MATCH(username) AGAINST('$query') 
                            ORDER BY relevance DESC");

    $users = "";

    while($row = $result->fetch_assoc()) {
        $users .= '<div class="user" id="user' . $row['id'] . '">
                        <img width="100px" height="100px" style="border-radius: 50%; margin-right: 15px; margin-bottom: 15px; cursor: pointer;" src="' . $row['profile_picture_path'] . '" onclick="window.location.href = \'user?id=' . $row['id'] . '\'">
                        <div style="margin-right: 10px;">
                            <h1 style="font-size: 1.7rem; margin-bottom: 4px;">' . $row['username'] . '</h1>
                            <p style="font-size: 0.9rem; color: gray;">' . $row['subscribers'] . ' subscribers</p>
                        </div>
                    </div>';
    }
}
else {
    header("Location: index.php");
    exit();
}
;?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - Search Results</title>
</head>
<body style="min-width: 800px;">
    <?php include 'header.php';?>

    <p style="font-size: 1.1rem; margin-left: 25%">Search results for <strong><?php echo $query;?></strong></p>

    <div class="search-results" id="search-results">
        <?php echo $users . $videos;?>
    </div>

    <script>
        let search_results = document.getElementById("search-results");
        if (search_results.childElementCount === 0) {
            let element = document.createElement("p");
            element.className = "no-results";
            element.textContent = "No results have been returned.";
            search_results.append(element);
        }

        function user(user_id) {
            window.location.href = "user?id=" + user_id;
        }

        function vid(vid_id) {
            window.location.href = "view_video?id=" + vid_id;
        }
    </script>
    <style>
        .search-results {
            margin-left: 25%;
            margin-top: 2rem;
        }

        .no-results {
            font-size: 1rem;
            color: #5c5c5c;
        }

        .user {
            display: flex;
            flex-direction: row;
            align-items: start;
            width: fit-content;
            margin-bottom: 2rem;
        }

        .vid {
            display: flex;
            flex-direction: row;
            align-items: start;
            justify-content: left;
            margin-bottom: 20px;
            width: 380px;
            max-width: 380px;
            overflow: hidden;
        }

        .thumbnail {
            width: 195px;
            min-width: 195px;
            height: 100px;
            border-radius: 10px;
            margin-bottom: 5px;
            margin-right: 10px;
            cursor: pointer;
        }

        .video_owner {
            display: flex;
            justify-content: left;
            align-items: center;
            cursor: pointer;
            width: fit-content;
        }

        .owner_pfp {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .owner_name {
            font-size: small;
            color: gray;
            padding: 5px 0px;
            word-wrap: break-word; 
            word-break: break-word;
        }

        .video_name {
            font-size: medium;
            word-wrap: break-word; 
            word-break: break-word;
        }

        .views_and_age {
            font-size: small;
            color: gray;
            padding: 5px 0px;
            word-wrap: break-word; 
            word-break: break-word;
        }
    </style>
</body>
</html>