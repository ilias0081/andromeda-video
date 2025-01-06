<?php
session_start();
include 'connect.php';
$account_created = FALSE;

if (!isset($_SERVER['HTTP_REFERER'])) {
    header("Location: index.php");
}

else if (isset($_POST['create_account'])) {
    $validFields = TRUE;
    
    if (strlen($_POST['username']) == 0 || strlen($_POST['password']) == 0) {
        $_SESSION['error_message'] = "Please fill out all the fields.";
        header('Location: login');
        $validFields = FALSE;
    }

    if (strtoupper($_POST['username']) == "SIGN IN" || strtoupper($_POST['username']) == "SIGN OUT") {
        $_SESSION['error_message'] = "Don't play games now.";
        header('Location: login');
        $validFields = FALSE;
    } 

    for ($i = 0; $i < strlen($_POST['username']); $i++) {
        if (!ctype_digit($_POST['username'][$i]) && $_POST['username'][$i] != ' ' && !ctype_alpha($_POST['username'][$i])) {
            $_SESSION['error_message'] = "Invalid username. Only a-z, A-Z, 0-9, and spaces are allowed.";
            header('Location: login');
            $validFields = FALSE;
        }
    }

    for ($i = 0; $i < strlen($_POST['password']); $i++) {
        if (!ctype_digit($_POST['password'][$i]) && !ctype_alpha($_POST['password'][$i])) {
            $_SESSION['error_message'] = "Invalid password. Only a-z, A-Z, and 0-9 are allowed.";
            header('Location: login');
            $validFields = FALSE;
        }
    }

    if ($validFields) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $checkUser = "SELECT * From users where username='$username'";
        $result = $connect->query($checkUser);

        if ($result->num_rows > 0) {                 
            $_SESSION['error_message'] = "Username already exists!";
            header('Location: login');
        }

        else {
            $insertQuery = "INSERT INTO users(username, password) VALUES ('$username', '$password')";

            if ($connect->query($insertQuery) == TRUE) {
                $account_created = TRUE;
            }
            else {
                echo "Error: ".$connect->error;
            }
        }
    }
} // idk

if (isset($_POST['log_in']) or $account_created) {
    $username = $_POST['username'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $connect->query($sql);

    $row = $result->fetch_assoc();
    if ($result->num_rows > 0 && password_verify($_POST['password'], $row['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['currentUser'] = $row;
        header("Location: index.php");
        exit();
    }
    else {
        $row = NULL;
        $_SESSION['error_message'] = "Bad username or password.";
        header('Location: login');
    }
}

?>