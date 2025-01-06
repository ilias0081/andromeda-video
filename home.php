<?php
session_start();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - Home</title>
</head>
<body style="min-width: 890px; margin: 0px 20px;">
    <?php include 'header.php'; ?>

    <template id="video_template">
        <img class="thumbnail" src="[[thumbnail]]" onclick="" width="250px" height="140px">
        <br>
        <h class="video_name">[[video_name]]</h>
        <br>
        <h class="views_and_age">[[views_and_age]]</h>
        <div class="video_owner">
            <img class="owner_pfp" src="[[owner_pfp]]" width="20px" height="20px">
            <h class="owner_name">[[owner_name]]</h>
        </div>
    </template> 

    <div class="video_area">
        <div class="vid" id="vid0"></div>
        <div class="vid" id="vid1"></div>
        <div class="vid" id="vid2"></div>
        <div class="vid" id="vid3"></div>
        <div class="vid" id="vid4"></div>
        <div class="vid" id="vid5"></div>
        <div class="vid" id="vid6"></div>
        <div class="vid" id="vid7"></div>
        <div class="vid" id="vid8"></div>
        <div class="vid" id="vid9"></div>
        <div class="vid" id="vid10"></div>
        <div class="vid" id="vid11"></div>
        <div class="vid" id="vid12"></div>
        <div class="vid" id="vid13"></div>
        <div class="vid" id="vid14"></div>
        <div class="vid" id="vid15"></div>
        <div class="vid" id="vid16"></div>
        <div class="vid" id="vid17"></div>
    </div>
    

    <script>
        var recData = new FormData();
        recData.append("amount", 18);

        fetch('getRec.php', {
            method: "POST",
            body: recData
        })
        .then(response => response.json())
        .then(data => {
            const temp = document.getElementById("video_template");
            console.log(data)

            for (let i = 0; i < data.length; i++) {
                let element = data[i];
                let clone = document.importNode(temp.content, true); 

                clone.querySelector('.thumbnail').src = element.thumbnail_path;
                clone.querySelector('.video_name').textContent = element.video_name;
                clone.querySelector('.owner_pfp').src = element.profile_picture_path;
                clone.querySelector('.owner_name').textContent = element.owner_name;
                clone.querySelector('.thumbnail').onclick = function() {window.location.href = "view_video?id=" + element.video_id;}
                clone.querySelector('.views_and_age').textContent = element.views + " views  |  " + element.age;
                clone.querySelector('.video_owner').onclick = function() {
                    window.location.href = 'user?id=' + element.owner_id;
                }

                document.getElementById('vid' + i).appendChild(clone);
            }
        })
        .catch(error => console.error('Error:', error));


    </script>

    <style>
        .video_area {
            display: grid;
            grid-template-columns: auto auto auto;
            margin: 10px 10px;
            column-gap: 25px;
            row-gap: 50px;
        }

        .vid {
            align-self: center;
            justify-self: center;
            justify-content: left;
            align-items: center;
        }

        .thumbnail {
            width: 250px;
            height: 140px;
            border-radius: 10px;
            margin-bottom: 5px;
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
        }

        .video_name {
            font-size: medium;
        }

        .views_and_age {
            font-size: small;
            color: gray;
            padding: 5px 0px;
        }
    </style>
</body>
</html>