<?php 
session_start();

include 'connect.php';

$id = intval($_GET['id']);
$result = $connect->query("SELECT * FROM users WHERE id=$id");
if ($result->num_rows > 0) {
    $user_profile = $result->fetch_assoc();

    $stats = array("videos" => 0, "views" => 0, "likes" => 0, "dislikes" => 0, "comments" => 0, "replies" => 0);
    
    $stats["videos"] = $connect->query("SELECT * FROM videos WHERE owner_id=" . $id)->num_rows;

    $stats["views"] = $connect->query("SELECT SUM(views) AS total FROM videos WHERE owner_id=" . $id)->fetch_assoc()['total'];
    if ($stats["views"] === NULL) $stats["views"] = 0;

    $stats["likes"] = $connect->query("SELECT SUM(likes) AS total FROM videos WHERE owner_id=" . $id)->fetch_assoc()['total'];
    if ($stats["likes"] === NULL) $stats["likes"] = 0;

    $stats["dislikes"] = $connect->query("SELECT SUM(dislikes) AS total FROM videos WHERE owner_id=" . $id)->fetch_assoc()['total'];
    if ($stats["dislikes"] === NULL) $stats["dislikes"] = 0;

    $stats["comments"] = $connect->query("SELECT * FROM comments WHERE owner_id=" . $id)->num_rows;
    $stats["replies"] = $connect->query("SELECT * FROM replies WHERE owner_id=" . $id)->num_rows;

    if (isset($_SESSION['currentUser']) && str_contains($_SESSION['currentUser']['subscribed_to'], "{\"id\":\"" . $id . "\"}")) 
        $subbed = TRUE;
    else 
        $subbed = FALSE;

    $subbedTo = json_decode($user_profile['subscribed_to'], true);
    $subList = "";

    foreach ($subbedTo as $i => $subscription) {
        $result = $connect->query("SELECT * FROM users WHERE id=" . $subscription['id']);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subList .='<div class="user-sub" id="user' . $row['id'] . '">
                            <img width="100px" height="100px" style="border-radius: 50%; margin-right: 15px; cursor: pointer;" src="' . $row['profile_picture_path'] . '" onclick="window.location.href = \'user?id=' . $row['id'] . '\'">
                            <div style="margin-right: 10px;">
                                <h1 style="font-size: 1.7rem; margin-bottom: 4px;">' . $row['username'] . '</h1>
                                <p style="font-size: 0.9rem; color: gray;">' . $row['subscribers'] . ' subscribers</p>
                            </div>
                         </div>';
        }
        if ($i === 49)
            break;
    }
}
else {
    header("Location: 404");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - <?php echo $user_profile['username'];?></title> 
</head>
<body style="min-width: 850px;">
    <?php include 'header.php';?>
    <main>
        <section class="profile-top-part">
            <div class="user">
                <img width="100px" height="100px" style="border-radius: 50%; margin-right: 25px;" src="<?php echo $user_profile['profile_picture_path'];?>">
                <div style="margin-right: 10px;">
                    <h1 style="font-size: 2.2rem; margin-bottom: 7px;"><?php echo $user_profile['username'];?></h1>
                    <p class="subtext" id="sub_count"><?php echo $user_profile['subscribers'];?> subscribers</p>
                </div>

                <button class="<?php echo $subbed ? "un" : "";?>sub_button" id="sub_button" onclick="<?php echo $subbed ? "un" : "";?>subscribe()">SUBSCRIBE<?php echo $subbed ? "D" : "";?></button>
            </div>
            <div class="stats">
                <h3>Stats:</h3>
                <div class="stats-list-container">
                    <div class="stats-list">
                        <p class="stats-list-item-id">Videos:</p>
                        <p class="stats-list-item"><?php echo $stats["videos"];?></p>
                        <p class="stats-list-item-id">Views:</p>
                        <p class="stats-list-item"><?php echo $stats["views"];?></p>
                        <p class="stats-list-item-id">Likes:</p>
                        <p class="stats-list-item"><?php echo $stats["likes"];?></p>
                    </div>
                    <div class="stats-list">
                        <p class="stats-list-item-id">Dislikes:</p>
                        <p class="stats-list-item"><?php echo $stats["dislikes"];?></p>
                        <p class="stats-list-item-id">Comments:</p>
                        <p class="stats-list-item"><?php echo $stats["comments"];?></p>
                        <p class="stats-list-item-id">Replies:</p>
                        <p class="stats-list-item"><?php echo $stats["replies"];?></p>
                    </div>
                </div>
            </div>
        </section>

        <hr class="divider">

        <h3 style="margin-bottom: 5px;">About</h3>

        <p class="text" id="about_text"><?php echo $user_profile['about'];?></p>
        <br><br>
        <h3>Videos</h3>
        <section id="videos" class="videos">

        </section>
        <br><br>
        <h3 style="cursor: pointer;" id="subscriptions-toggle" onclick="subscriptionsToggle()">▼ Subscriptions</h3>
        <section class="subscriptions" id="subscriptions-section">
            <?php echo $subList;?>
        </section>
    </main>

    <template id="video_template">
        <div class="vid">
            <img class="thumbnail" src="[[thumbnail]]" onclick="" width="250px" height="140px">
            <br>
            <h class="video_name">[[video_name]]</h>
            <p style="font-size: small; color: gray;" class="views_and_age">[[views_and_age]]</p>
        </div>
    </template>

    <script>
        function subscriptionsToggle() {
            subToggle = document.getElementById("subscriptions-toggle");
            
            if (subToggle.textContent.includes("▼")) {
                subToggle.textContent = "▲ Subscriptions";
                document.getElementById("subscriptions-section").style.display = 'flex';
            }
            else if (subToggle.textContent.includes("▲")) {
                subToggle.textContent = "▼ Subscriptions";
                document.getElementById("subscriptions-section").style.display = 'none';
            }
        }

        function getUserVideos() {
            let about_text = document.getElementById("about_text");
            if (about_text.textContent === "") 
                about_text.textContent = "(empty)";

            let formData = new FormData();
            formData.append("id", <?php echo $id;?>)

            fetch('getOwnVideos.php', {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const temp = document.getElementById("video_template");

                let i = 0
                let j = data.length - 1

                if (j >= 0) {
                    while (i < j) {
                        let temp = data[i]
                        data[i] = data[j]
                        data[j] = temp
                        i++
                        j--
                    }
                }

                for (let i = 0; i < data.length; i++) {
                    let element = data[i];
                    let clone = document.importNode(temp.content, true); 

                    clone.querySelector('.thumbnail').src = element.thumbnail_path;
                    clone.querySelector('.video_name').textContent = element.video_name;
                    clone.querySelector('.views_and_age').textContent = element.views + (parseInt(element.views) == 1 ? " view" : " views") + "  |  " + element.age;

                    clone.querySelector('.thumbnail').onclick = function() {
                        window.location.href = "view_video?id=" + element.video_id;
                    } 

                    document.getElementById('videos').appendChild(clone);
                }
            });
        }
        getUserVideos();

        function subscribe() {
            var canSubscribe = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;

            if (canSubscribe) {
                var subData = new FormData();
                subData.append("subto", <?php echo $id;?>);

                fetch('subscribeTo.php', {
                    method : "POST",
                    body: subData
                })
                .then(response => response.text())
                .then(data => {

                    let subval = parseInt(data, 10)
                    if (subval != -1) {
                        document.getElementById("sub_count").innerHTML = subval + " subscribers"

                        let sub_button = document.getElementById("sub_button")
                        sub_button.className = "unsub_button"
                        sub_button.innerHTML = "SUBSCRIBED"
                        sub_button.onclick = unsubscribe
                    }
                })
                .catch(error => console.error('Error:', error));

            } else {
                alert("Please log in or create an account to use this feature.");
            }
        }

        function unsubscribe() {
            var subData = new FormData();
            subData.append("unsubto", <?php echo $id;?>);

            fetch('subscribeTo.php', {
                method : "POST",
                body: subData
            })
            .then(response => response.text())
            .then(data => {
                
                let subval = parseInt(data, 10)
                if (subval != -1) {
                    document.getElementById("sub_count").innerHTML = subval + " subscribers"

                    let sub_button = document.getElementById("sub_button")
                    sub_button.className = "sub_button"
                    sub_button.innerHTML = "SUBSCRIBE"
                    sub_button.onclick = subscribe
                }
            })
            .catch(error => console.error('Error:', error));
        }


    </script>

    <style>
        main {
            margin: 50px 10%;
        }

        .divider {
            border: 1px solid lightgray; 
            width: 100%; 
            margin: 20px 0px;
        }

        .profile-top-part {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: start;
            justify-content: space-between;
        }

        .user {
            display: flex;
            flex-direction: row;
            align-items: start;
            width: fit-content;
            margin-bottom: 2rem;
        }

        .subtext {
            font-size: 1rem;
            color: gray;
            word-wrap: break-word;
            word-break: break-word;
        }

        .text {
            font-size: 1rem;
            word-wrap: break-word;
            word-break: break-word;
        }

        .sub_button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 1.1rem;
            font-weight: 550;
            color: white;
            background-color: red;
            padding: 12px 20px;
            border: 2px solid transparent;
            border-radius: 30px;
            margin-left: 20px;
            align-self: center;
        }
        .sub_button:hover {
            cursor: pointer;
            border: 2px solid black;
            top: -2;
            left: -2;
        }

        .unsub_button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 1.1rem;
            font-weight: 550;
            color: black;
            background-color: lightgray;
            padding: 12px 20px;
            border: 2px solid transparent;
            border-radius: 30px;
            margin-left: 20px;
            align-self: center;
        }
        .unsub_button:hover {
            cursor: pointer;
            border: 2px solid black;
            top: -2;
            left: -2;
        }

        .stats {
            width: fit-content;
            margin-left: 15px;
        }

        .stats-list-container {
            display: flex;
            flex-direction: row;
            align-items: start;
            justify-content: left;
        }

        .stats-list {
            display: grid;
            grid-template-columns: auto auto;
            margin: 10px 0px;
            column-gap: 25px;
            column-width: 85px;
            row-gap: 3px;
            margin-right: 20px;
        }

        .stats-list-item-id {
            font-size: 1rem;
            margin-right: 0.5rem;
            width: 85px;
        }

        .stats-list-item { 
            font-size: 1rem;
            color: gray;
            width: 100px;
            text-align: right;
        }

        .videos, .subscriptions {
            width: 100%;
            height: fit-content;
            min-height: 255px;
            overflow: auto;
            background-color:rgb(251, 251, 251);
            padding: 20px 20px;
            margin: 20px 5px;
            margin-bottom: 60px;
            display: flex;
            align-items: center;
            border-radius: 5px;
            border: 2px inset white;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
            transition: box-shadow 0.2s ease;
        }
        .videos:hover {
            box-shadow: inset 0 0 13px rgba(0, 0, 0, 0.5);
        }

        .subscriptions {
            display: none;
            min-height: 175px;
            background-color: white;
            border: 1px solid white;
        }

        .vid {
            margin: 15px 15px;
            font-family: 'Poppins', sans-serif;
        }

        .thumbnail {
            width: 250px;
            height: 140px;
            border-radius: 10px;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .user-sub {
            display: flex;
            flex-direction: row;
            align-items: start;
            width: fit-content;
            margin-right: 5rem;
        }
    </style>
</body>
</html>