<?php 
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit();
}
else {
    include 'connect.php';

    $ftpServer = "";
    $ftpUsername = "";
    $ftpPassword = "";

    if (isset($_FILES['file'])) {

        $file = $_FILES['file'];
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        // thumbnail part
        if (isset($_FILES['file2'])) {
            $thumb = $_FILES['file2'];
            $thumbDir = 'thumbnails/';
            $thumbFile = $thumbDir . basename($thumb['name']);
            $thumbFile = str_replace(' ', '', $thumbFile);

            while (file_exists($thumbFile)) { 
                $randomChars = "";
                for ($i = 0; $i < 20; $i++) {
                    $randomChars .= $chars[rand(0, 61)];
                } 
    
                $pos = strrpos($thumbFile, '.');
    
                $part1 = substr($thumbFile, 0, $pos);
                $part2 = substr($thumbFile, $pos);
    
                $thumbFile = $part1 . $randomChars . $part2;
            }
        }

        // video part
        $uploadDir = '/videos/';
        $uploadFile = $uploadDir . basename($file['name']);
        $uploadFile = str_replace(' ', '', $uploadFile);

        if ($_POST['name'] == "") { 
            $_SESSION["uploadError"] = "No name was set.";
            header("Location: uploadVideo.php");
            exit();
        }
        if ($_POST['description'] == "") {
            $_POST['description'] = "New video uploaded by " . $_SESSION['username'];
        }

        $ftpConn = ftp_connect($ftpServer, 21, 300);
        if (!$ftpConn || !ftp_login($ftpConn, $ftpUsername, $ftpPassword)) {
            $_SESSION["uploadError"] = "Could not connect to FTP server.";
            header("Location: uploadVideo.php");
            exit();
        }

        while (file_exists("videos" . $uploadFile)) {
            $randomChars = "";
            for ($i = 0; $i < 20; $i++) {
                $randomChars .= $chars[rand(0, 61)];
            }

            $pos = strrpos($uploadFile, '.');

            $part1 = substr($uploadFile, 0, $pos);
            $part2 = substr($uploadFile, $pos);

            $uploadFile = $part1 . $randomChars . $part2;
        }

        $_POST['name'] = mysqli_real_escape_string($connect, $_POST['name']);
        $_POST['description'] = mysqli_real_escape_string($connect, $_POST['description']);

        if (/*move_uploaded_file($file['tmp_name'], $uploadFile)*/ ftp_put($ftpConn, $uploadFile, $file['tmp_name'], FTP_BINARY)) {
            $uploadSQLpath = "" . $uploadFile;
            if (isset($thumb) && move_uploaded_file($thumb['tmp_name'], $thumbFile)) 
                    $videoQuery = "INSERT INTO videos(owner_id, video_path, thumbnail_path, name, description) VALUES ('{$_SESSION['currentUser']['id']}', '$uploadSQLpath', '$thumbFile', '{$_POST['name']}', '{$_POST['description']}')";
            else 
                $videoQuery = "INSERT INTO videos(owner_id, video_path, name, description) VALUES ('{$_SESSION['currentUser']['id']}', '$uploadSQLpath', '{$_POST['name']}', '{$_POST['description']}')";

            if ($connect->query($videoQuery) === TRUE) {
                $_SESSION["uploadError"] = "";
                header("Location: view_video?id=" . $connect->insert_id);
            }
        } else {
            $_SESSION["uploadError"] = "No video was uploaded.";
            header("Location: uploadVideo.php");
            exit();
        }
    } else {
        $_SESSION["uploadError"] = "No video was uploaded.";
        header("Location: uploadVideo.php");
        exit();
    }
}
?>