<?php 
session_start();

if (!isset($_SESSION["uploadError"]))
    $_SESSION["uploadError"] = "";

if (!isset($_SESSION['currentUser'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - Upload Video</title>
</head>
<body> 
    <div class="title">
        <img class="logo" src="website_images/website logo.png">
        <h1 style="font-family: 'Franklin Gothic', sans-serif;">Andromeda</h1>
    </div>

    <div class="uploading_overlay" id="uploading_overlay">
        <div class="uploading_container" id="uploading_container">
            <h5 style="margin-right: 12px; font-size: 1rem; font-family: 'Franklin Gothic', sans-serif;">Uploading...</h5>
            <img width="15px" height="15px" src="website_images/loading-icon.gif">
        </div>
    </div>
    <style>
        .uploading_overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: transparent;
            cursor: default;
            z-index: 2;
            align-items: center; 
            justify-content: center;
            place-items: center; 
            text-align: center;
        }
        .uploading_container {
            display: flex; 
            position: fixed;
            z-index: 3;
            height: 1.1rem;
            top: 50%;
            flex-direction: row; 
            align-items: center; 
            border: 2px outset rgb(175, 175, 175);
            border-radius: 10px;
            background-color: white;
            padding: 15px 15px;
            box-shadow: outset 0 0 10px rgba(0, 0, 0, 0.5);;
        }
    </style>


    <form id="entire_thing" class="entire_thing" action="uploadVideo2.php" method="POST" enctype="multipart/form-data">

        <h1 style="font-family: 'Poppins', sans-serif; margin-bottom: 40px;">Upload Video</h1>

        <div class="upload_part">
            <div class="uploading_video" style="margin-right: 95px; word-wrap:break-word; max-width: 400px;">
                <!-- uploading video -->
                <div class="video" id="video_container">

                </div>

                <div class="button_x_row">
                    <label class="video_button" for="file">Choose Video</label>
                    <img src="website_images/x_symbol.png" width="15px" height="15px" class="x_button" onclick="video_x_button()">
                </div>
                <p class="file_name_container" id="video_file_name_container"><strong>File Name:</strong> No File Chosen</p>

                <input type="file" name="file" id="file" accept=".mp4,.webm,.mov,.ogv">
            </div>

            <div class="uploading_thumbnail" style="word-wrap:break-word; max-width: 400px;">
                <!-- uploading thumbnail -->
                <div class="thumbnail" id="thumbnail_container">

                </div>
                <div class="button_x_row">
                    <label class="video_button" for="file2">Choose Thumbnail</label>
                    <img src="website_images/x_symbol.png" width="15px" height="15px" class="x_button" onclick="thumbnail_x_button()">
                </div>
                <p class="file_name_container" id="thumbnail_file_name_container"><strong>File Name:</strong> No File Chosen</p>


                <input type="file" name="file2" id="file2" accept=".png,.jpeg,.gif,.bmp,.svg,.webp,.ico">
            </div>
        </div>

        <br>

        <div class="input_part">
            <p class="error_text"><?php echo $_SESSION['uploadError']; $_SESSION['uploadError'] = "";?></p>
            <input class="name" name="name" id="name" placeholder="Name" maxlength="75">
            <textarea id="des" class="description" name="description" id="description" placeholder="Description" maxlength="1000" rows="8", cols="75"></textarea>

            <div class="bottom_row">
                <p></p>

                <button type="submit" class="upload_button">
                    <img width="25px" height="25px" src="website_images/upload_symbol.png">
                    <h4 style="font-family: 'Poppins', sans-serif; margin-left: 12px;">Upload Video</h4>
                </button>

                <div onclick="window.location.href = 'dashboard'" class="cancel_button">
                    <h4 style="text-shadow: black;">Cancel</h4>
                </div>
                <p></p>
            </div>
        </div> 

    </form>


    <script>
        document.getElementById("entire_thing").addEventListener("submit", function() {
            document.getElementById("uploading_overlay").style.display = "grid";
        });

        document.getElementById('file').hidden = true
        document.getElementById('file2').hidden = true

        document.getElementById("file").onchange = function () {
            var allowed = ["video/mp4", "video/webm", "video/quicktime", "video/ogg"]
            var video = this.files[0]

            if (video) {
                if (!allowed.includes(video.type)) {
                    alert(video.type + " is not allowed. Please only upload .mp4, .webm, .ogv, or .mov");
                    this.value = "";
                }
            }

            if (this.value != "") {
                let video_self = document.getElementById("video_self")

                if (video_self == null) {
                    let container = document.getElementById("video_container")
                    let newVideo = document.createElement("video")
                    newVideo.src = URL.createObjectURL(video)
                    newVideo.width = 400;
                    newVideo.height = 225;
                    newVideo.id = "video_self"
                    newVideo.controls = true
                    container.appendChild(newVideo)
                }
                else {
                    video_self.src = URL.createObjectURL(video)
                }

                document.getElementById("video_file_name_container").innerHTML = "<strong>File Name:   </strong>" + video.name
            }

        }

        function video_x_button() {
            var input = document.getElementById("file")
            if (input.files.length != 0) {
                input.value = "";
                document.getElementById("video_container").innerHTML = ""
                document.getElementById("video_file_name_container").innerHTML = "<strong>File Name:   </strong> No File Chosen"
            }
        }

        document.getElementById("file2").onchange = function () {
            var file = this.files[0];
            var allowed = ["image/png", "image/jpeg", "image/gif", "image/bmp", "image/svg", "image/webp", "image/x-icon"]

            if (file) {
                if (!allowed.includes(file.type)) {
                    alert(file.type + " is not a valid image type.")
                    this.value = ""
                }
            }

            if (this.value != "") {
                let img_self = document.getElementById("thumbnail_self")

                if (img_self == null) {
                    let container = document.getElementById("thumbnail_container")
                    let newImg = document.createElement("img")
                    newImg.src = URL.createObjectURL(file)
                    newImg.width = 400;
                    newImg.height = 225;
                    newImg.id = "thumbnail_self"
                    newImg.style = "border-radius: 15px;"
                    container.appendChild(newImg)
                }
                else {
                    img_self.src = URL.createObjectURL(file)
                }

                document.getElementById("thumbnail_file_name_container").innerHTML = "<strong>File Name:   </strong>" + file.name
            }
        }

        function thumbnail_x_button() {
            var input = document.getElementById("file2")
            if (input.files.length != 0) {
                input.value = ""
                document.getElementById("thumbnail_container").innerHTML = ""
                document.getElementById("thumbnail_file_name_container").innerHTML = "<strong>File Name:   </strong> No File Chosen"
            }
        }
    </script>


    <style>
        body {
            display: grid;
            place-items: center;
            margin: 5px 30px;
        }

        .title {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .entire_thing {
            display: flex;
            align-items: center;
            flex-direction: column;
            padding-left: 60px;
            padding-right: 60px;
            padding-bottom: 30px;
            padding-top: 25px;
            margin: 15px 15px;
            border: 1px solid black;
            border-radius: 40px;
            background-color: rgb(252, 252, 255);
        }

        .file_name_container {
            word-wrap: break-word;
        }

        .upload_part {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video {
            width: 400px;
            max-width: 400px;
            height: 225px;
            background-color: black;
            margin-bottom: 15px;
        }

        .video_button {
            padding: 5px 5px;
            background-color: lightgray;
            border: 1px solid black;
            border-radius: 7px;
            transition: background-color 0.2s ease;
        }
        .video_button:hover {
            box-shadow: 0 0 0 1px black;
            background-color: white;
            cursor: pointer;
        }

        .thumbnail {
            width: 400px;
            height: 225px;
            background-color: black;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .input_part {
            display: grid;
            margin-left: 20px;
            align-items: top;
        }

        .name {
            width: 900px;
            font-size: large;
            border-radius: 10px;
            padding: 5px 10px;
            margin-bottom: 20px;
        }

        .description {
            width: 890px;
            font-family: 'Poppins', sans-serif;
            font-size: large;
            border-radius: 10px;
            padding: 15px 15px;
            margin-bottom: 20px;
        }

        .x_button {
            background-color: red;
            border: 1px solid black;
            padding: 3px 3px;
            border-radius: 3px;
        }
        .x_button:hover {
            background-color: white;
            cursor: pointer;
        }

        .button_x_row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .upload_button {
            display: flex;
            align-items: center;
            padding: 0px 30px;
            background-color: #4fff61;
            border: 2px solid black;
            border-radius: 5px;
            transition: background-color 0.15s linear;
        }
        .upload_button:hover {
            background-color: white;
            box-shadow: 0 0 0 1px;
            cursor: pointer;
        }

        .cancel_button {
            display: flex;
            place-items: center;
            justify-content: center;
            width: 165px;
            height: 50px;
            border: 2px solid black;
            border-radius: 5px;

            font-family: 'Poppins', sans-serif;
            color: white;
            background-color: #940000;
            transition: background-color 0.2s ease;
        }
        .cancel_button:hover {
            box-shadow: 0 0 0 1px black;
            background-color: #ff4f4f;
            cursor: pointer;
        }

        .bottom_row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        .error_text {
            color: red;
            justify-self: left;
            justify-content: left;
        }
    </style>
</body>
</html>