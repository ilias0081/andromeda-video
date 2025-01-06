<?php 
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="website_images/website logo.png" type="image/png">
    <title>Andromeda - Sign In</title> 
</head>
<body>
    <div class="title" onclick="window.location.href = 'home'">
        <img class="logo" width="170px" height="85px" src="website_images/website logo.png">
        <h1 style="font-family: 'Franklin Gothic', sans-serif;">Andromeda</h1>
    </div>
    <div class="box">
        <h1>Sign In<br><br></h1> 
        <h5>Enter credentials to login or to create an account.</h5>
        <h5 style="color: red;">
            <?php 
                if (isset($_SESSION['error_message'])) {
                    if ($_SESSION['error_message'] != '') {
                        echo $_SESSION['error_message'];
                    }
                    else {
                        echo "&nbsp";
                    }
                    $_SESSION['error_message'] = '';
                }
            ?>
        </h5> 
        <form action="register.php" method="POST">
            <div class="username-section">
                <h4>Username</h4>
                <input class="inputs" maxlength="20" id="username" name="username" onkeyup="validInput(this)", onkeydown="validInput(this)", onkeypress="validInput(this)">
            </div>

            <div class="username-section">
                <h4>Password</h4>
                <input type="password" maxlength="100" id="password" name="password" class="inputs", onkeyup="validInput(this)", onkeydown="validInput(this)", onkeypress="validInput(this)">
            </div>

            <h5>Warning:  Only input characters a-z, A-Z, and 0-9. Only input spaces in the username. Any other character will be removed upon proceeding.</h5>
            <br><br><br>
            <div class="buttons">
                <button class="button" style="flex: 0.5; margin-right: 50px;" name="log_in">Log In</button>
                <button class="button" style="flex: 0.5; margin-left: 50px;" name="create_account">Create Account</button>
            </div>
        </form>
    </div>

    <script>

        function validInput(input) {
            input.value = input.value.replace(/[^0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ]/, '')

        }

    </script>
    
    <style>
        body {
            height: 100%;
            display: grid;
            place-items: center;
            background-color: white;
            font-family: 'Poppins', sans-serif;
        }
        .title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .title:hover {
            cursor: pointer;
        }

        .logo {
            padding-right: 20px;
        }

        .box {
            width: 500px;
            height: 5325x;
            padding: 30px 50px;
            border: 1px solid black;
            background-color: rgb(252, 252, 255);
            border-radius: 25px;
            margin: 10px 10px;
        }

        .username-section {
            display: flex;
            align-items: center;
            padding: 5px 0px;
        }

        .inputs {
            padding: 7px;
            width: 400px;
            border: 1px solid #cccccce1;
            font-size: 16px;
            margin-left: 40px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .button {
            border-radius: 6px;
            height: 50px;
            font-size: 100%;
            border: 2px solid black;
            transition: background-color 0.17s ease;
        }
        .button:hover {
            cursor: pointer;
            box-shadow: 0 0 0 1px black;
            background-color: white;
        }

    </style>
</body>
</html>