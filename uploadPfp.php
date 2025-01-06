<?php 
session_start();

if (!isset($_SERVER['HTTP_REFERER'])) {
    header("Location: index.php");
}

else if (isset($_FILES['pfp'])) {
    include 'connect.php';

    $file = $_FILES['pfp'];
    $uploadDir = 'profile_pictures/';
    $uploadFile = $uploadDir . basename($file['name']);
    $uploadFile = str_replace(' ', '', $uploadFile);

    while (file_exists($uploadFile)) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $randomChars = "";
        for ($i = 0; $i < 20; $i++) {
            $randomChars .= $chars[rand(0, 61)];
        } 

        $pos = strrpos($uploadFile, '.'); 

        $part1 = substr($uploadFile, 0, $pos);
        $part2 = substr($uploadFile, $pos);

        $uploadFile = $part1 . $randomChars . $part2;
    }

    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        echo "File is valid, and was successfully uploaded.\n";
        $sql = "UPDATE users SET profile_picture_path='$uploadFile' WHERE id=" . $_SESSION['currentUser']['id'];
        if ($connect->query($sql) === TRUE) {
            $_SESSION['currentUser']['profile_picture_path'] = $uploadFile;
        }
    } else {
        echo "No file was uploaded.\n";
    }
} else {
    echo "No file was uploaded.";
} 
?>