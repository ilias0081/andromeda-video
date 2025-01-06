<?php
session_start();

if (!isset($_SESSION['currentUser'])) {
    header("Location: login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - Dashboard</title>
</head> 
<body style="margin: 20px 20px;">
    <div class="title" onclick="window.location.href = 'index.php'">
        <img class="logo" src="website_images/website logo.png" width="100px">
        <h1 style="font-family: 'Franklin Gothic', sans-serif; font-size: 1.75rem;">Andromeda</h1>
    </div>

    <div class="pfp"> 
        <image class="usericon" src=<?php echo $_SESSION['currentUser']['profile_picture_path'];?>>
        <div class="change_pfp" onclick="changePfp()"><image style="width: 23px; height: 23px; position: absolute;" src="website_images/plus_symbol.png"></div>
    </div>

    <h1 style="font-family: 'Poppins', sans-serif; font-weight: normal;">
        Welcome, <?php echo $_SESSION['username'];?>
    </h1>
    <span class="upload-and-feedback">
        <form method="POST", action="uploadVideo.php">
            <button type="submit" class="upload_video">
                <img width="25px" height="25px" src="website_images/upload_symbol.png">
                <h4 style="font-family: 'Poppins', sans-serif; margin-left: 12px;">Upload Video</h4>
            </button>
        </form>
        <button class="send-feedback-main-button" onclick="feedbackOverlay()">Send Feedback</button>
        <button class="edit-about-button" onclick="editAboutOverlay()">Edit About Section</button>

    </span>

    <br>

    <template id="video_template">
        <div class="vid">
            <img class="thumbnail" src="[[thumbnail]]" onclick="" width="250px" height="140px">
            <br>
            <h class="video_name">[[video_name]]</h>
            <p style="font-size: small; color: gray;" class="views_and_age">[[views_and_age]]</p>
        </div>
    </template>

    <h2>Your Videos</h2>
    <div id="videos" class="videos"></div>

    <form method="POST", action="logout.php">
        <button type="submit" class="Log-Out">Sign Out</button>
    </form>

    <input type="file" id="pfpInput" style="display: none;" accept="image/*" onchange="uploadPfp()" enctype="multipart/form-data">

    <div class="overlay" id="overlay">
        <div class="feedback-box">
            <h1 style="font-family: 'Poppins', sans-serif; font-weight: normal;">Send Feedback</h1>
            <textarea class="text" id="feedback_text" placeholder="Tell me any improvements to be made ..." maxlength="950"></textarea>
            <p class="feedback-msg" id="feedback-msg">&nbsp;</p>
            <div class="feedback-buttons">
                <p></p>
                <button class="feedback-submit" id="feedback-submit" onclick="sendFeedback()">Submit</button>
                <button class="feedback-cancel" id="feedback-cancel" onclick="feedbackOverlayOff()">Cancel</button>
                <p></p>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay2">
        <div class="edit-about-box">
            <h1 style="font-family: 'Poppins', sans-serif; font-weight: normal;">Edit About</h1>
            <textarea class="text" id="edit_about_text" placeholder="Write the about section of your profile ..." maxlength="950"></textarea>
            <p class="edit-about-msg" id="edit-about-msg">&nbsp;</p>
            <div class="edit-about-buttons">
                <p></p>
                <button class="edit-about-submit" id="edit-about-submit" onclick="sendEdit()">Edit</button>
                <button class="edit-about-cancel" id="edit-about-cancel" onclick="editAboutOverlayOff()">Cancel</button>
                <p></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("edit_about_text").value = "<?php echo $_SESSION['currentUser']['about'];?>";

        let getOwnVideosFormData = new FormData();
        getOwnVideosFormData.append("id", <?php echo $_SESSION['currentUser']['id'];?>)

        fetch('getOwnVideos.php', {
            method: "POST",
            body: getOwnVideosFormData
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

        function changePfp() {
            document.getElementById('pfpInput').click()
        }

        function uploadPfp() {
            var element = document.getElementById('pfpInput');
            var file = element.files[0];
            allowed = ["image/png", "image/jpeg", "image/gif", "image/bmp", "image/bmp", "image/svg", "image/webp", "image/x-icon", "image/avif"]

            if (file) {
                if (!allowed.includes(file.type)) {
                    alert(file.type + " is not a valid image type.")
                    element.value = ""
                    return
                }

                var form_data = new FormData();
                form_data.append('pfp', file);

                fetch('uploadPfp.php', {
                    method: 'POST', 
                    body: form_data
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }

        function editAboutOverlay() {
            document.getElementById("overlay2").style.display = 'block';
        }

        function editAboutOverlayOff() {
            let send_edit = document.getElementById("edit-about-submit");
            let cancel_edit = document.getElementById("edit-about-cancel");
            send_edit.onclick = function(){sendEdit();}
            send_edit.style.opacity = "100%";
            cancel_edit.onclick = function() {editAboutOverlayOff();}
            cancel_edit.style.opacity = "100%";

            document.getElementById("edit-about-msg").innerHTML = "&nbsp;";
            document.getElementById("edit_about_text").value = "<?php echo $_SESSION['currentUser']['about'];?>";
            document.getElementById("overlay2").style.display = 'none';
        }

        function sendEdit() {
            let text = document.getElementById("edit_about_text").value;

            var formData = new FormData();
            formData.append("text", text);
            fetch('editAbout.php', {
                method: 'POST', 
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById("edit-about-msg").textContent = data;
                if (!data.includes("fail")) {
                    let send_edit = document.getElementById("edit-about-submit");
                    let cancel_edit = document.getElementById("edit-about-cancel");
                    send_edit.onclick = null;
                    send_edit.style.opacity = "50%";
                    cancel_edit.onclick = null;
                    cancel_edit.style.opacity = "50%";

                    setTimeout(function() {editAboutOverlayOff(); location.reload()}, 2500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function feedbackOverlay() {
            document.getElementById("overlay").style.display = 'block';
        }

        function feedbackOverlayOff() {
            let send_feedback = document.getElementById("feedback-submit");
            let cancel_feedback = document.getElementById("feedback-cancel");
            send_feedback.onclick = function(){sendFeedback();}
            send_feedback.style.opacity = "100%";
            cancel_feedback.onclick = function() {feedbackOverlayOff();}
            cancel_feedback.style.opacity = "100%";

            document.getElementById("feedback-msg").innerHTML = "&nbsp;";
            document.getElementById("feedback_text").value = "";
            document.getElementById("overlay").style.display = 'none';
        }

        function sendFeedback() {
            let text = document.getElementById("feedback_text").value;

            var formData = new FormData();
            formData.append("text", text);
            fetch('feedback.php', {
                method: 'POST', 
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById("feedback-msg").textContent = data;
                if (!data.includes("fail")) {
                    let send_feedback = document.getElementById("feedback-submit");
                    let cancel_feedback = document.getElementById("feedback-cancel");
                    send_feedback.onclick = null;
                    send_feedback.style.opacity = "50%";
                    cancel_feedback.onclick = null;
                    cancel_feedback.style.opacity = "50%";

                    setTimeout(function() {feedbackOverlayOff();}, 2500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>

    <style>
        .html, .body {
            font-family: "Poppins", sans-serif;
            min-width: 600px;
            min-height: 600px;
        }

        body {
            min-width: 600px;
        }

        h2 {
            font-family: "Poppins", sans-serif;
            font-weight: normal;
            
        }

        .title {
            display: flex;
            align-items: left;
            margin-bottom: 40px;
        }
        .title:hover {
            cursor: pointer;
        }
        .logo {
            margin-right: 10px;
            height: auto;
            width: 100px;
        }

        .pfp {
            position: relative;
        }

        .usericon {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .change_pfp {
            position: absolute;
            background-color: cyan;
            top: 71px;
            left: 71px;
            width: 23px;
            height: 23px;
            border-radius: 25%;
            border: 1px solid black;
            box-shadow: 0 0 0 1px black;
            transition: background-color 0.1s linear, box-shadow 0.1s linear;
        }

        .change_pfp:hover {
            box-shadow: 0 0 0 2px black;
            cursor: pointer;
            background-color: white;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-right: 10px;
        }
        .website-name {
            font-family: 'Franklin Gothic', sans-serif;
            font-size: 1.75rem;
        }

        .upload_video {
            display: flex;
            align-items: center;
            padding: 0px 12px;
            background-color: #b3b4ff;
            border: 2px solid black;
            border-radius: 5px;
            box-shadow: 0 0 0 0px black;
            transition: background-color 0.175s ease, box-shadow 0.175s ease;
        }
        .upload_video:hover {
            cursor: pointer;
            box-shadow: 0 0 0 1px black;
            background-color: white;
        }

        .videos {
            width: 95%;
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

        .Log-Out {
            color: black;
            font-size: 18px;
            background-color: #eeeeee;
            border-radius: 10px;
            border: 1px solid black;
            box-shadow: 0 0 0 0px black;
            padding: 8px 8px;
            transition: background-color 0.15s ease, box-shadow 0.15s ease;
        }
        .Log-Out:hover {
            box-shadow: 0 0 0 1px black;
            cursor: pointer;
            background-color: white;
        }

        .overlay, .overlay2 {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1; 
        }

        .feedback-box, .edit-about-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: baseline;
            position: fixed;
            z-index: 2;
            background-color: white;
            top: 30%;
            left: 30%;
            right: 30%;
            bottom: 30%;
            min-width: 450px;
            min-height: 450px;
            padding: 20px 20px;
            border-radius: 30px;
            border: 2px solid lightgray;
            overflow: visible;
        }

        @media screen and (max-height: 800px) {
            .feedback-box, .edit-about-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: baseline;
                position: fixed;
                z-index: 2;
                background-color: white;
                top: 10%;
                left: 10%;
                right: 10%;
                bottom: 10%;
                min-width: 300px;
                min-height: 300px;
                padding: 15px 15px;
                border-radius: 30px;
                border: 2px solid lightgray;
                overflow: visible;
            }
        }

        .text {
            width: 90%;
            height: 50%;
            font-family: 'Poppins', sans-serif;
            font-size: large;
            border-radius: 10px;
            padding: 15px 15px;
            margin-bottom: 20px;
        }

        .feedback-buttons, .edit-about-buttons {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 90%;
            margin-top: 10px;
        }

        .feedback-submit, .feedback-cancel, .edit-about-submit, .edit-about-cancel {
            font-size: 1.2rem;
            background-color: #eeeeee;
            opacity: 100%;
            padding: 7px 10px;
            box-shadow: 0 0 0 0px black;
            border: 1px solid black;
            border-radius: 5px;
            transition: background-color 0.1s ease, box-shadow 0.1s ease;
        }

        .feedback-submit:hover, .feedback-cancel:hover, .edit-about-submit:hover, .edit-about-cancel:hover {
            background-color: white;
            box-shadow: 0 0 0 1px black;
            cursor: pointer;
        }

        .feedback-msg {
            font-family: "Poppins", sans-serif;
            font-size: 0.8rem;
        }

        .upload-and-feedback {
            display: flex;
            flex-direction: row;
            align-items: center;
            width: fit-content;
        }
        
        .send-feedback-main-button, .edit-about-button {
            font-size: 1.3rem;
            padding: 10px 8px;
            margin-left: 50px;
            background-color: #b3b4ff;
            border: 1px solid black;
            box-shadow: 0 0 0 0px black;
            border-radius: 5px;
            transition: background-color 0.15s ease, box-shadow 0.15s ease;
        }
        .send-feedback-main-button:hover, .edit-about-button:hover {
            background-color: white;
            box-shadow: 0 0 0 1px black;
            cursor: pointer;
        }
    </style>
</body>
</html>