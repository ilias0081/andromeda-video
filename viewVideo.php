<?php 
session_start();


include 'connect.php';
$id = intval($_GET['id']);

$sql = "SELECT * FROM videos WHERE id='$id'";
$result = $connect->query($sql);

if ($result->num_rows > 0) {
    $video = $result->fetch_assoc(); 
} else {
    header("Location: 404");
    exit();
}

if (isset($video)) {
    // getting likes
    $owner = $video['owner_id'];
    $sql = "SELECT * FROM users WHERE id=$owner";
    $owner = $connect->query($sql)->fetch_assoc();
    $video['views']++;
    $connect->query("UPDATE videos SET views = views + 1 WHERE id=" . $video['id']);

    if (isset($_SESSION['currentUser'])) {
        if (str_contains($_SESSION['currentUser']['subscribed_to'], "{\"id\":\"" . $video['owner_id'] . "\"}")) {
            $subbed = TRUE;
        }
        else {
            $subbed = FALSE;
        } 

        $liked_json = json_decode($_SESSION['currentUser']['reactions'], true);
        $liked = 3; 

        foreach ($liked_json as $react) {
            if ($react['id'] == strval($video['id'])) {
                if ($react['type'] == "like")
                    $liked = 1;
                else if ($react['type'] == "dislike")
                    $liked = 2;
            }
        }
    }
    else {
        $subbed = FALSE;
        // liked=1 means like, liked=2 means dislike, liked=3 means neither
        $liked = 3;
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
    <title>Andromeda - <?php echo $video['name'];?></title>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="main_body">
        <section class="left_side">
            <div class="video-container">
                <video id="video_element" class="video" width="100%" controls>
                        <source src="<?php echo $video['video_path'];?>">
                </video>
            </div>
            <br>
            <div>
                <h1 style="font-size: x-large;"><?php echo $video['name'];?></h1>
            </div>
            <br>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;"> <!--row1-->
                <div class="owner" onclick="window.location.href = 'user?id=<?php echo $owner['id'];?>'">
                    <img class="owner_icon" src=<?php echo $owner['profile_picture_path'];?>>
                    <div class="name_and_sub">
                        <h2 style="font-size: 19px; font-weight: normal;"><?php echo $owner['username'];?></h2>
                        <h2 style="font-size: small; font-weight: normal; color: gray;" id="sub_count"><?php echo $owner['subscribers'];?> subscribers</h2> 
                    </div>
                </div>
                <div>
                    <h2 class="views"><?php echo $video['views'];?> views</h2>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 40px;"> <!--row2-->
                <button class="<?php echo ($subbed ? "un" : "")?>sub_button" id="sub_button" name="sub_button" onclick="<?php echo ($subbed ? "un" : "")?>subscribe()">
                    SUBSCRIBE<?php echo ($subbed ? "D" : "")?>
                </button>
                <div class="likes_and_dislikes">
                    <button class="like_button" onclick="<?php echo ($liked == 1 ? "un" : "");?>like()" id="like_button">
                        <img src="website_images/like<?php echo ($liked == 1 ? "d" : "");?>_icon.png" width="35px" height="35px" id="like_button_img">
                    </button>
                    <h2 class="likes" id="like_count"><?php echo $video['likes'];?></h2>
                    <button class="like_button" onclick="dislike()" id="dislike_button">
                        <img src="website_images/dislike<?php echo ($liked == 2 ? "d" : "");?>_icon.png" width="35px" height="35px" id="dislike_button_img">
                    </button>
                    <h2 class="likes" id="dislike_count"><?php echo $video['dislikes'];?></h2>
                </div>

            </div>
            <p class="date">Uploaded <?php 

                $uploaded = new DateTime($video['date'], new DateTimeZone('UTC'));
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $age = $now->diff($uploaded);

                if ($age->y > 0)
                    echo ($age->y == 1) ? $age->y . " year" : $age->y . " years";
                else if ($age->m > 0)
                    echo ($age->m == 1) ? $age->m . " month" : $age->m . " months";
                else if ($age->d > 0)
                    echo ($age->d == 1) ? $age->d . " day" : $age->d . " days";
                else if ($age->h > 0)
                    echo ($age->h == 1) ? $age->h . " hour" : $age->h . " hours";
                else if ($age->i > 0) 
                    echo ($age->i == 1) ? $age->i . " minute" : $age->i . " minutes";
                else if ($age->s >= 0) 
                    echo $age->s . " seconds";

            ?> ago</p>

            <p class="des"><?php echo $video['description'];?></p>

            <!-- Comments Section -->
            <div class="comments-title" style="display: flex; align-items: end; justify-content: space-between; margin-bottom: 5px;">
                <h1>Comments</h1>
                <div style="display: flex; flex-direction:row;">
                    <p class="refresh-comments" onclick="getComments()">Refresh Comments</p>
                    <p class="refresh-comments" onclick="getRec()">Refresh Recommended</p>
                </div>
            </div>

            <hr style="border: 1px solid lightgray; width: 100%; margin-bottom: 5px;">

            <p class="comment-error" id="comment-error">&nbsp;</p>

            <div class="comment-input">
                <img class="comment-pfp" src="<?php 
                    if (isset($_SESSION['currentUser']['profile_picture_path'])) {
                        echo $_SESSION['currentUser']['profile_picture_path'];
                    } else {
                        echo "website_images/default_pfp.png";
                    }
                ?>">

                <input class="comment-input-box" name="comment-input-box" id="comment-input-box" maxlength="250" placeholder="Add a comment..." onkeypress="keyPressComment(event)">
                <button class="comment-button" name="comment-button" id="comment-button" onclick="addComment()">Comment</button>
            </div>

            <br>
            <br>
            
            <div id="comments_section"></div>
        </section>
        

        <hr class="left_right_divider">

        <section class="right_side" id="right_side">

        </section>
    </main>

    <template id="comment-template">
        <div class="comment">
            <img class="comment-pfp" src="[[comment_owner_pfp]]">
            <div class="comment-text-container">
                <div class="comment-name-age">
                    <p class="comment-owner-name">[[comment_owner_name]]</p>
                    <p class="comment-age">[[comment_age]]</p>
                </div>
                <p class="comment-text">[[comment_text]]</p>
                <div class="comment-heart-reply">
                    <img class="comment-heart" width="15px" height="15px" src="website_images/heart.png" onclick="heart(this, 'comment')">
                    <p class="comment-heart-count">0</p>
                    <p class="comment-reply-button" onclick="showReplyInput(this)">Reply</p>
                </div>
                <div class="comment-reply-input">
                    <img class="reply-pfp" src="<?php 
                        if (isset($_SESSION['currentUser']['profile_picture_path'])) {
                            echo $_SESSION['currentUser']['profile_picture_path'];
                        } else {
                            echo "website_images/default_pfp.png";
                        }
                    ?>">

                    <input class="reply-input-box" name="reply-input-box" id="reply-input-box" maxlength="250" placeholder="Add a reply..." onkeypress="keyPressReply(event, this)">
                    <button class="reply-cancel-button" name="reply-cancel-button" id="reply-cancel-button" onclick="hideReplyInput(this)">Cancel</button>
                    <button class="reply-button" name="reply-button" id="reply-button" onclick="addReply(this)">Reply</button>
                    </div>
                <p class="comment-view-replies" onclick="toggleDisplayReplies(this)">[[comment-view-replies]]</p>
                <div class="comment-replies">

                </div>
            </div>
        </div>
    </template>

    <template id="rec_video_template">
        <div class="rec_vid">
            <img class="rec_thumbnail" src="[[rec_thumbnail]]" onclick="" width="160px" height="90px">
            <div class="rec_vid_info">
                <h class="rec_video_name">[[video_name]]</h>
                <br>
                <h class="rec_views_and_age">[[views_and_age]]</h>
                <div class="rec_video_owner">
                    <img class="rec_owner_pfp" src="[[rec_owner_pfp]]" width="20px" height="20px">
                    <h class="rec_owner_name">[[rec_owner_name]]</h>
                </div>
            </div>
        </div>
    </template> 

    <template id="reply-template">
        <div class="reply">
            <img class="reply-pfp" src="[[comment_owner_pfp]]">
            <div class="reply-text-container">
                <div class="reply-name-age">
                    <p class="reply-owner-name">[[comment_owner_name]]</p>
                    <p class="reply-age">[[comment_age]]</p>
                </div>
                <p class="reply-text">[[comment_text]]</p>
                <div class="reply-heart-container">
                    <img class="reply-heart" width="15px" height="15px" src="website_images/heart.png" onclick="heart(this, 'reply')">
                    <p class="reply-heart-count">0</p>
                </div>
            </div>
        </div>
    </template>

    <script>
        function toggleDisplayReplies(element) {
            let comment_view_replies = element;
            let comment_replies = element.nextElementSibling;
            const currentScroll = window.scrollY;

            if (comment_view_replies.textContent.includes("View")) {
                comment_view_replies.textContent = comment_view_replies.textContent.replace("▼ View", "▲ Hide");
                comment_replies.style.display = 'block';

                // getReplies
                if (comment_replies.childElementCount === 0) {
                    let comment = element.closest(".comment");
                    let comment_id = parseInt(comment.id.replace("comment", ""));

                    var formData = new FormData();
                    formData.append("comment_id", comment_id);

                    fetch('getReplies.php', {
                        method : "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (Object.keys(data).length != 0) {
                            const temp = document.getElementById("reply-template");

                            for (let i = 0; i < data.length; i++) {
                                let clone = document.importNode(temp.content, true);

                                clone.querySelector(".reply-pfp").src = data[i].pfp_path;
                                clone.querySelector(".reply-owner-name").textContent = data[i].owner_name;
                                clone.querySelector(".reply-age").textContent = data[i].age;
                                clone.querySelector(".reply-text").textContent = data[i].text_content;
                                clone.querySelector(".reply-heart-count").textContent = data[i].hearts;
                                clone.querySelector(".reply").id = "reply" + data[i].id.toString();
                                clone.querySelector(".reply-pfp").onclick = clone.querySelector(".reply-owner-name").onclick = function() {
                                    window.location.href = "user?id=" + data[i].owner_id;
                                }
                                if (data[i].hearted) {
                                    clone.querySelector(".reply-heart").src = "website_images/hearted.png";
                                    clone.querySelector(".reply-heart").onclick = function() {
                                        eval("unheart(this, 'reply')")
                                    }
                                }

                                comment.querySelector(".comment-replies").appendChild(clone);
                            }
                        }
                        else {

                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
            else if (comment_view_replies.textContent.includes("Hide")) {
                comment_view_replies.textContent = comment_view_replies.textContent.replace("▲ Hide", "▼ View");
                comment_replies.style.display = 'none';
            }
            window.scrollTo(0, currentScroll);
        }

        function keyPressReply(event, element) {
            if (event.key === "Enter")
                addReply(element);
        }

        function addReply(element) {
            let comment = element.closest(".comment");
            let textbox = comment.querySelector(".reply-input-box");

            if (textbox.value == "") {
                alert("Please enter text into the reply box.");
                return;
            }

            let comment_id = parseInt(comment.id.replace("comment", ""));
            var formData = new FormData();
            formData.append("comment_id", comment_id);
            formData.append("text_content", textbox.value);

            fetch("addReply.php", {
                method : "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (Object.keys(data).length != 0) {
                    textbox.value = "";
                    comment.querySelector(".comment-reply-input").style.display = 'none';
                    let comment_view_replies = comment.querySelector(".comment-view-replies");

                    if (window.getComputedStyle(comment_view_replies).display === 'none') {
                        comment_view_replies.style.display = 'block';
                        comment_view_replies.textContent = "▼ View " + data.comment_replies + " Replies"
                    }
                    else
                        comment_view_replies.textContent = comment_view_replies.textContent.replace(/[0-9]/, data.comment_replies.toString());

                    let comment_replies = comment_view_replies.nextElementSibling;
                    
                    if (comment_view_replies.textContent.includes("View") && comment_replies.childElementCount === 0)
                        toggleDisplayReplies(comment_view_replies);

                    else {
                        comment_view_replies.textContent = comment_view_replies.textContent.replace("▼ View", "▲ Hide");
                        comment_replies.style.display = 'block';
                        let clone = document.importNode(document.getElementById("reply-template").content, true);

                        clone.querySelector(".reply-pfp").src = data.pfp_path;
                        clone.querySelector(".reply-owner-name").textContent = data.owner_name;
                        clone.querySelector(".reply-age").textContent = data.age;
                        clone.querySelector(".reply-text").textContent = data.text_content;
                        clone.querySelector(".reply-heart-count").textContent = data.hearts;
                        clone.querySelector(".reply").id = "reply" + data.id.toString();
                        clone.querySelector(".reply-pfp").onclick = clone.querySelector(".reply-owner-name").onclick = function() {
                            window.location.href = "user?id=" + data.owner_id;
                        }

                        comment_replies.insertBefore(clone, comment_replies.firstChild);
                    }

                } else
                    alert("Failed to reply to comment, please try again.");
            })
            .catch(error => console.error('Error:', error));

        }

        function showReplyInput(element) {
            if (<?php echo isset($_SESSION['currentUser']) ? "true" : "false";?>)
                element.closest(".comment-text-container").querySelector(".comment-reply-input").style.display = 'flex';
            else
                alert("Please log in or create an account to use this feature.");
        }

        function hideReplyInput(element) {
            element.closest(".comment-reply-input").style.display = 'none';
        }

        function getRec() {
            var container = document.getElementById("right_side");
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }

            var recData = new FormData();
            recData.append("amount", 20);

            fetch('getRec.php', {
                method: "POST",
                body: recData
            })
            .then(response => response.json())
            .then(data => {
                const currentScroll = window.scrollY;
                const temp = document.getElementById("rec_video_template");

                for (let i = 0; i < data.length; i++) {
                    let element = data[i];
                    let clone = document.importNode(temp.content, true); 

                    clone.querySelector('.rec_thumbnail').src = element.thumbnail_path;
                    clone.querySelector('.rec_video_name').textContent = element.video_name;
                    clone.querySelector('.rec_owner_pfp').src = element.profile_picture_path;
                    clone.querySelector('.rec_owner_name').textContent = element.owner_name;
                    clone.querySelector('.rec_thumbnail').onclick = function() {window.location.href = "view_video?id=" + element.video_id;}
                    clone.querySelector('.rec_views_and_age').textContent = element.views + " views  |  " + element.age;
                    clone.querySelector('.rec_vid').id = 'vid' + i;
                    clone.querySelector('.rec_video_owner').onclick = function() {
                        window.location.href = 'user?id=' + element.owner_id;
                    }
                
                    container.appendChild(clone);
                }
                window.scrollTo(0, currentScroll);
            })
            .catch(error => console.error('Error:', error));
        }

        getRec();

        function heart(element, type) {
            let canHeart = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;

            if (!canHeart) {
                alert("Please log in or create an account to use this feature.");
                return;
            }

            let heart_image = element;
            let comment = element.closest("." + type);

            let formData = new FormData();
            formData.append("for", type);
            formData.append("type", "heart");
            formData.append("id", parseInt(comment.id.replace(type, "")));

            fetch("commentReaction.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                //console.log(data);
                if (data.success.trim() == "true") {
                    heart_image.src = "website_images/hearted.png";
                    heart_image.onclick = function() {eval("unheart(this, '" + type + "')")}
                    comment.querySelector("." + type + "-heart-count").textContent = data.hearts
                }
            })
            .catch(error => console.error('Error:', error));

        }

        function unheart(element, type) {
            let canUnHeart = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;

            if (!canUnHeart) {
                alert("Please log in or create an account to use this feature.");
                return;
            }

            let heart_image = element;
            let comment = element.closest("." + type);

            let formData = new FormData();
            formData.append("for", type);
            formData.append("type", "unheart");
            formData.append("id", parseInt(comment.id.replace(type, "")));

            fetch("commentReaction.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                //console.log(data);
                if (data.success.trim() == "true") {
                    heart_image.src = "website_images/heart.png";
                    heart_image.onclick = function() {eval("heart(this, '" + type + "')")}
                    comment.querySelector("." + type + "-heart-count").textContent = data.hearts
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function getComments() {
            const currentScroll = window.scrollY;
            const container = document.getElementById('comments_section');
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }

            var commentData = new FormData();
            commentData.append("count", 20);
            commentData.append("video_id", <?php echo $video['id'];?>)

            fetch("getComments.php", {
                method: "POST",
                body: commentData
            })
            .then(response => response.json())
            .then(data => {
                //console.log(data);
                const temp = document.getElementById("comment-template")

                for (let i = 0; i < data.length; i++) {
                    let clone = document.importNode(temp.content, true); 
                    clone.querySelector(".comment-pfp").src = data[i].pfp_path
                    clone.querySelector(".comment-owner-name").textContent = data[i].owner_name
                    clone.querySelector(".comment-text").textContent = data[i].text_content
                    clone.querySelector(".comment-age").textContent = data[i].age
                    clone.querySelector(".comment-heart-count").textContent = data[i].hearts
                    clone.querySelector(".comment-pfp").onclick = clone.querySelector(".comment-owner-name").onclick = function() {
                        window.location.href = "user?id=" + data[i].owner_id;
                    }
                    if (data[i].hearted) {
                        clone.querySelector(".comment-heart").src = "website_images/hearted.png";
                        clone.querySelector(".comment-heart").onclick = function() {eval("unheart(this, 'comment')")}
                    }
                    if (data[i].replies > 0)
                        clone.querySelector(".comment-view-replies").textContent = "▼ View " + data[i].replies + " Replies";
                    else
                        clone.querySelector(".comment-view-replies").style.display = 'none';

                    clone.querySelector(".comment").id = "comment" + data[i].id.toString();
                    document.getElementById("comments_section").appendChild(clone);
                } 
            })
            .catch(error => console.error('Error:', error));
            window.scrollTo(0, currentScroll);
        }
        getComments();

        function keyPressComment(event) {
            if (event.key === "Enter")
                addComment();
        }

        function addComment() {
            const currentScroll = window.scrollY;
            let canComment = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;

            if (!canComment) {
                alert("Please log in or create an account to use this feature.");
                return;
            }

            let textbox = document.getElementById("comment-input-box");
            if (textbox.value == "") {
                document.getElementById("comment-error").textContent = "Please enter text into the comment box.";
                return;
            }

            commentData = new FormData();
            commentData.append("text", textbox.value);
            commentData.append("video_id", <?php echo $video["id"];?>);
            
            fetch("addComment.php", {
                method : "POST",
                body: commentData
            })
            .then(response => response.json())
            .then(data => {
                if (Object.keys(data).length != 0) {
                    textbox.value = "";
                    document.getElementById("comment-error").innerHTML = "&nbsp;";

                    let clone = document.importNode(document.getElementById("comment-template").content, true); 
                    clone.querySelector(".comment-pfp").src = data.pfp_path
                    clone.querySelector(".comment-owner-name").textContent = data.owner_name
                    clone.querySelector(".comment-text").textContent = data.text_content
                    clone.querySelector(".comment-age").textContent = data.age
                    clone.querySelector(".comment-heart-count").textContent = data.hearts
                    clone.querySelector(".comment-pfp").onclick = clone.querySelector(".comment-owner-name").onclick = function() {
                        window.location.href = "user?id=" + data.owner_id;
                    }
                    clone.querySelector(".comment-view-replies").textContent = "▼ View 0 Replies";;
                    clone.querySelector(".comment-view-replies").style.display = 'none';

                    let parent = document.getElementById("comments_section");
                    clone.querySelector(".comment").id = "comment" + data.id.toString();
                    parent.insertBefore(clone, parent.firstChild);
                } else {
                    document.getElementById("comment-error").textContent = "Failed to post comment, please try again.";
                }
            })
            .catch(error => console.error('Error:', error));
            window.scrollTo(0, currentScroll);
        }

        function subscribe() {
            var canSubscribe = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;

            if (canSubscribe) {
                var subData = new FormData();
                subData.append("subto", <?php echo $video['owner_id'];?>);

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
            subData.append("unsubto", <?php echo $video['owner_id'];?>);

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

        function like() {
            let canLike = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>
            
            if (canLike) {
                var likeData = new FormData();
                likeData.append("video_id", <?php echo $video["id"];?>);
                likeData.append("type", "like");

                fetch("videoReaction.php", {
                    method : "POST",
                    body: likeData
                })
                .then(response => response.json())
                .then(data => {
                    if (data["success"] == "true") {
                        document.getElementById("like_button_img").src = "website_images/liked_icon.png";
                        document.getElementById("dislike_button_img").src = "website_images/dislike_icon.png";
                        document.getElementById("like_count").innerHTML = data["likes"];
                        document.getElementById("dislike_count").innerHTML = data["dislikes"];
                        document.getElementById("like_button").onclick = unlike;
                        document.getElementById("dislike_button").onclick = dislike;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            else {
                alert("Please log in or create an account to use this feature.");
            }
        }

        function unlike() {
            let canUnLike = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>
            
            if (canUnLike) {
                var likeData = new FormData();
                likeData.append("video_id", <?php echo $video["id"];?>);
                likeData.append("type", "unlike");

                fetch("videoReaction.php", {
                    method : "POST",
                    body: likeData
                })
                .then(response => response.json())
                .then(data => {
                    if (data["success"] == "true") {
                        document.getElementById("like_button_img").src = "website_images/like_icon.png";
                        document.getElementById("like_count").innerHTML = data["likes"];
                        document.getElementById("like_button").onclick = like;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            else {
                alert("Please log in or create an account to use this feature.");
            }
        }

        function dislike() {
            let canDisike = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>
            
            if (canDisike) {
                var likeData = new FormData();
                likeData.append("video_id", <?php echo $video["id"];?>);
                likeData.append("type", "dislike");

                fetch("videoReaction.php", {
                    method : "POST",
                    body: likeData
                })
                .then(response => response.json())
                .then(data => {
                    if (data["success"] == "true") {
                        document.getElementById("like_button_img").src = "website_images/like_icon.png";
                        document.getElementById("dislike_button_img").src = "website_images/disliked_icon.png";
                        document.getElementById("like_count").innerHTML = data["likes"];
                        document.getElementById("dislike_count").innerHTML = data["dislikes"];
                        document.getElementById("like_button").onclick = like;
                        document.getElementById("dislike_button").onclick = undislike;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            else {
                alert("Please log in or create an account to use this feature.");
            }
        }

        function undislike() {
            let canUnDisike = <?php echo (isset($_SESSION['currentUser']) ? "true" : "false");?>;
            
            if (canUnDisike) {
                var likeData = new FormData();
                likeData.append("video_id", <?php echo $video["id"];?>);
                likeData.append("type", "undislike");

                fetch("videoReaction.php", {
                    method : "POST",
                    body: likeData
                })
                .then(response => response.json())
                .then(data => {
                    if (data["success"] == "true") {
                        document.getElementById("dislike_button_img").src = "website_images/dislike_icon.png";
                        document.getElementById("dislike_count").innerHTML = data["dislikes"];
                        document.getElementById("dislike_button").onclick = dislike;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            else {
                alert("Please log in or create an account to use this feature.");
            }
        }

    </script>

    <style>
        body {
            min-width: 600px;
        }

        .main_body {
            display: flex;
            flex-direction: row;
            align-items: start;
            justify-content: space-between;
        }

        .left_side {
            display: flex;
            flex-direction: column;
            width: 62%;
            margin-left: 55px;
            margin-top: 30px;   
        }

        .right_side {
            margin-top: 30px;
            margin-right: 30px;
            justify-content: left;
        }

        @media screen and (max-width: 1260px) {
            .main_body {
                display: flex;
                flex-direction: column;
                align-items: start;
                justify-content: left;
            }

            .left_side {
                display: flex;
                flex-direction: column;
                width: 95%;
                margin: 30px 30px; 
            }

            .left_right_divider {
                border: 1px solid lightgray; 
                width: 95%; 
                margin-bottom: 10px;
                margin-right: 30px;
                margin-left: 30px;
            }

            .right_side {
                margin-top: 30px;
                margin-left: 30px;
                justify-content: left;
            }
        }

        .video {
            position: absolute;
            z-index: 0;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px; 
            object-fit: contain;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            overflow: hidden;
            background: black;
            border-radius: 10px; 
            justify-self: left;
        }

        .owner {
            display: flex;
            flex-direction: row;
            border: 1px solid transparent;
            border-radius: 20px;
            padding-left: 5px;
            padding-right: 25px;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .owner:hover {
            cursor: pointer;
            border: 1px solid lightgray;
        }

        .owner_icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .name_and_sub {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
            justify-content: left;
        }

        .views {
            background-color: #fbfbfb;
            font-size: 18px;
            font-weight: normal;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid lightgray;
        }

        .sub_button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 14px;
            font-weight: 550;
            color: white;
            background-color: red;
            padding: 7px 15px;
            border: 2px solid transparent;
            border-radius: 30px;
            margin-left: 5px;
        }
        .sub_button:hover {
            cursor: pointer;
            border: 2px solid black;
            top: -2;
            left: -2;
        }

        .unsub_button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 14px;
            font-weight: 550;
            color: black;
            background-color: lightgray;
            padding: 7px 12px;
            border: 2px solid transparent;
            border-radius: 30px;
            margin-left: 5px;
        }
        .unsub_button:hover {
            cursor: pointer;
            border: 2px solid black;
            top: -2;
            left: -2;
        }

        .likes_and_dislikes {
            display: flex;
            align-items: center;
            justify-content: right;
        }
        .like_button {
            border: none;
            background-color: white;

            padding: 5px 5px;
            margin-right: 10px;
            transition: background-color 0.15s ease;
        }
        .like_button:hover {
            cursor: pointer;
            background-color: #f3f3f3;
        }
        .likes {
            font-size: 17px;
            font-weight: normal;
            margin-right: 25px;
        }

        .des {
            margin-bottom: 75px; 
            word-wrap: break-word; 
            font-size: 1rem;
        }

        .date {
            color: gray; 
            margin-bottom: 25px; 
            font-size: 1rem;
        }

        .refresh-comments {
            font-size: 0.75rem; 
            color: blue; 
            text-decoration: underline;
            margin-left: 1rem;
        }
        .refresh-comments:hover {
            cursor: pointer;
        }

        .comment-input {
            display: flex;
            flex-direction: row;
            margin-top: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-reply-input {
            display: none;
            flex-direction: row;
            margin-top: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-pfp {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
        }
        .comment-pfp:hover {
            cursor: pointer;
        }

        .reply-pfp {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 12px;
        }
        .reply-pfp:hover {
            cursor: pointer;
        }

        .comment-input-box, .reply-input-box {
            flex-grow: 1;
            font-size: 1rem;
            border: 1px solid transparent;
            border-bottom: 1px solid gray;
            padding: 2px 3px;
            transition: box-shadow 0.125s linear;
            margin-right: 15px;
        }

        .comment-input-box:focus, .reply-input-box:focus {
            border: 1px solid transparent;
            border-bottom: 1px solid black;
            outline: none;
            box-shadow: 0 1px 0 0 black;
        }

        .comment-button, .reply-button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 14px;
            color: white;
            font-weight: bold;
            background-color:rgb(10, 10, 255);
            border-radius: 20px;
            padding: 8px 10px;
            border: 1px solid black;
            transition: background-color 0.125s ease;
            margin-right: 5px;
        }

        .comment-button:hover, .reply-button:hover {
            background-color: #3366ff;
            cursor: pointer;
        }

        .reply-cancel-button {
            font-family: 'Proxima Nova', sans-serif;
            font-size: 14px;
            color: black;
            font-weight: bold;
            background-color: lightgray;
            border-radius: 20px;
            padding: 8px 10px;
            border: 1px solid black;
            transition: background-color 0.125s ease;
            margin-right: 5px;
        }
        .reply-cancel-button:hover {
            background-color: white;
            cursor: pointer;
        }
        
        .comment-error {
            margin-top: 5px;
            color: red;
            font-size: 13px;
        }

        .comment {
            display: flex;
            width: 100%;
            flex-direction: row;
            align-items: start;
            margin-bottom: 40px;
        }

        .reply {
            display: flex;
            width: 100%;
            flex-direction: row;
            align-items: start;
            margin-bottom: 20px;
        }

        .comment-text-container, .reply-text-container {
            flex-grow: 1;
        }

        .comment-text, .reply-text {
            word-wrap: break-word; 
            word-break: break-word; 
            font-size: 0.9375rem;
            overflow: hidden;
        }

        .comment-name-age, .reply-name-age {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .comment-owner-name, .reply-owner-name {
            font-weight: bold; 
            font-size: 0.9375rem;
        }
        .comment-owner-name:hover, .reply-owner-name:hover {
            cursor: pointer;
        }

        .comment-age, .reply-age {
            margin-left: 7px;
            color: gray;
            font-size: 0.75rem;
        }

        .comment-heart-reply {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-top: 7px;
            margin-bottom: 5px;
        }

        .reply-heart-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-top: 6px;
            margin-bottom: 5px;
        }

        .comment-heart-count, .reply-heart-count {
            color: gray; 
            font-size: 0.8125rem; 
            margin-right: 15px;
        }

        .comment-heart, .reply-heart {
            margin-right: 7px;
        }
        .comment-heart:hover, .reply-heart:hover {
            cursor: pointer;
        }

        .comment-reply-button {
            color: black;
            font-size: 0.8125rem;
        }
        .comment-reply-button:hover {
            color:rgb(49, 49, 49);
            cursor: pointer;
        }

        .comment-view-replies {
            display: block;
            color: #3366ff;
            font-size: 0.8125rem;
            transition: color 0.1s ease;
            width: fit-content;
        }
        .comment-view-replies:hover {
            color:rgb(111, 147, 255);
            cursor: pointer;
        }

        .comment-replies {
            display: none;
            margin-top: 5px;
            margin-left: 10px;
        }

        .rec_vid {
            display: flex;
            flex-direction: row;
            align-items: start;
            justify-content: left;
            margin-bottom: 20px;
            width: 380px;
            max-width: 380px;
            overflow: hidden;
        }

        .rec_thumbnail {
            width: 195px;
            min-width: 195px;
            height: 100px;
            border-radius: 10px;
            margin-bottom: 5px;
            margin-right: 10px;
            cursor: pointer;
        }

        .rec_video_owner {
            display: flex;
            justify-content: left;
            align-items: center;
            cursor: pointer;
            width: fit-content;
        }

        .rec_owner_pfp {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .rec_owner_name {
            font-size: small;
            color: gray;
            padding: 5px 0px;
            word-wrap: break-word; 
            word-break: break-word;
        }

        .rec_video_name {
            font-size: medium;
            word-wrap: break-word; 
            word-break: break-word;
        }

        .rec_views_and_age {
            font-size: small;
            color: gray;
            padding: 5px 0px;
            word-wrap: break-word; 
            word-break: break-word;
        }

    </style>
</body>
</html>