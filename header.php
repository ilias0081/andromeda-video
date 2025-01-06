<?php 

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header("Location: index.php");
    die();
}

else if (isset($_SESSION['currentUser'])) {
    include 'connect.php';

    if (!isset($_SESSION['searchQuery']))
        $_SESSION['searchQuery'] = "";

    $ussss = $_SESSION['username'];
    $sql = "SELECT * From users where username='$ussss'";
    $result = $connect->query($sql);
    
    if ($result->num_rows == 0) { 
        $_SESSION['username'] = null;
        $_SESSION['currentUser'] = null;
    }
    
    $subbedTo = json_decode($_SESSION['currentUser']['subscribed_to'], true);
    $sub_list = "";

    foreach ($subbedTo as $i => $subscription) {
        $result = $connect->query("SELECT * FROM users WHERE id=" . $subscription['id']);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sub_list .= 
            '<li class="menu-sub" onclick="window.location.href = \'user?id=' . $row['id'] . '\'">
                <img width="25px" height="25px" class="menu-sub-pfp" src="' . $row['profile_picture_path'] . '">
                <p class="menu-sub-name">' . $row['username'] . '</p>
            </li>';
        }
        if ($i === 49)
            break;
    }
} else {
    $sub_list = '<p style="font-size: 0.75rem; color: gray;">Sign-in to see subscriptions</p>';
}

?>

<header> 
    <div class="header-left">
        <!-- sidebar menu -->
        <div class="side-menu" id="side_menu">
            <button class="menu-close-button" onclick="menu_close()">
                ✕
            </button>
            <ul class="menu-options">
                <li class="menu-li" onclick="mainPage()">
                    <img width="20px" height="20px" src="website_images/home_icon.png" style="margin-right: 10px">
                    <p>Home</p>
                </li>
                <li class="menu-li" onclick="aboutPage()">
                    <img width="20px" height="20px" src="website_images/about_icon.png" style="margin-right: 10px">
                    <p>About</p>
                </li>
                <li class="menu-li" onclick="userPage()">
                    <img width="20px" height="20px" src="website_images/your_profile_icon.png" style="margin-right: 10px">
                    <p><?php echo isset($_SESSION['currentUser']) ? "Your Profile" : "Sign In";?></p>
                </li>
                <li style="padding: 15px 5px;">
                    <p><strong>Subscriptions</strong></p>
                    <hr style="border: 1px solid lightgray; margin: 5px 0px;">
                    <ul id="menu-subscriptions">
                      <?php echo $sub_list;?>
                    </ul>
                </li>
            </ul>
        </div>

        <button class="side-menu-button" onclick="menu_open()">☰</button>

        <style>
            .side-menu {
                display: 'none';
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                min-width: 225px;
                max-width: 225px;
                box-shadow: 10px 0px 15px -5px rgba(0, 0, 0, 0.0);
                background-color: white;
                border-right: 1px solid lightgray;
                padding: 30px 15px;
                z-index: 1;
                transform: translateX(-100%);
                transition: transform 0.5s ease;
                cursor: default;
                overflow-y: auto;
            }

            .side-menu-button {
                outline: none;
                border: none;
                background-color: white;
                color: black;
                font-size: 1.5rem;
                border-radius: 50%;
                padding: 7px 12px;
                margin-right: 10px;
            }
            .side-menu-button:hover {
                background-color: #eeeeee;
                cursor: pointer;
            }

            .menu-close-button {
                display: flex;
                background-color: white;
                border: none;
                font-size: large;
                justify-self: right;
                padding: 5px 10px;
                border-radius: 50%;
            }
            .menu-close-button:hover {
                background-color: #eeeeee;
                cursor: pointer;
            }

            .menu-options {
                list-style-type:none;
            }

            .menu-li {
                display: flex;
                flex-direction: row;
                align-items: center;
                padding: 5px 5px;
            }
            .menu-li:hover {
                background-color: #eeeeee;
                cursor: pointer;
            }

            .menu-sub {
                display: flex;
                flex-direction: row;
                align-items: center;
                width: 100%;
                word-wrap: break-word;
                word-break: break-word;
                padding: 5px 12px;
            }
            .menu-sub:hover {
                background-color: #eeeeee;
                cursor: pointer;
            }

            .menu-sub-pfp {
                width: 25px;
                height: 25px;
                border-radius: 50%;
                margin-right: 8px;
            }

            .menu-sub-name {
                font-size: 0.9rem;
                color: black;
            }
        </style>

        <script>
            function menu_open() {
                const menu = document.getElementById("side_menu");
                menu.style.display = 'block';
                menu.style.transform = "translateX(0%)";
                menu.style.boxShadow = "10px 10px 15px 5px rgba(0, 0, 0, 0.2)";
                
            }

            function menu_close() {
                const menu = document.getElementById("side_menu");
                menu.style.transform = "translateX(-100%)";
                setTimeout(function() {menu.style.boxShadow = "10px 10px 15px 5px rgba(0, 0, 0, 0.0)";}, 500);
            }
        </script>

        <!-- -->

        <img class="logo" src="website_images/website logo.png" width="120px" alt="Logo" onclick="mainPage()">
        <h1 class="website-name" onclick="mainPage()">Andromeda</h1>
    </div>
    <div class="header-center">
        <input type="text" placeholder="Search" class="search-bar" id="search-bar">
        <img id="search-img-button" width="35px" height="35" src="website_images/search_icon.png" onclick="searchGo()">
    </div>
    <div class="header-right">
        <div class="userbutton" onclick="<?php 
            if (!isset($_SESSION['username'])) {
                echo "loginPage()";
            }
            else {
                echo "userPage()";
            }
        ?>">
            <img class="usericon" src="<?php 
                if (isset($_SESSION['currentUser']['profile_picture_path'])) {
                    echo $_SESSION['currentUser']['profile_picture_path'];
                } else {
                    echo "website_images/default_pfp.png";
                }
            ?>">
            <h class="username">
                <?php 
                if (!isset($_SESSION['username'])) {
                    echo "Sign In";
                }
                else {
                    echo $_SESSION['username'];
                }
                ?>
            </h>
        </div>
    </div>
    <script>
        document.getElementById("search-bar").value = "<?php echo isset($_SESSION['searchQuery']) ? $_SESSION['searchQuery'] : "";?>";

        document.getElementById("search-bar").addEventListener("keydown", function(event) {
            if (event.key === "Enter" && document.getElementById("search-bar").value != "")
                window.location.href = "search.php?query=" + encodeURIComponent(document.getElementById("search-bar").value);
        });

        function searchGo() {
            if (document.getElementById("search-bar").value == "")
                return;
            window.location.href = "search.php?query=" + encodeURIComponent(document.getElementById("search-bar").value);
        }

        function mainPage() {
            window.location.href = "home";
        }

        function loginPage() {
            window.location.href = "login";
        }

        function aboutPage() {
            window.location.href = "about";
        }

        function userPage() {
            window.location.href = "dashboard";
        }
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            margin-bottom: 30px;
            width: 100%;
            min-width: inherit;
        }

        .header-left {
            display: flex; 
            flex-basis: 25%;
            align-items: center;
            justify-content: left;
        }
        .header-left:hover {
            cursor: pointer;
        }

        .logo {
            margin-right: 10px;
        }

        .website-name { 
            font-family: 'Franklin Gothic', sans-serif;
            font-size: 1.75rem;
        }

        .header-center {
            flex-basis: 50%;
            display: flex;
            justify-content: center;

        }

        .search-bar {
            padding: 12px;
            width: 28rem;
            border: 1px solid #cccccce1;
            border-radius: 25px;
            font-size: 16px;
            justify-self: center;
        }

        #search-img-button {
            width: 35;
            height: 35px; 
            align-self: center; 
            margin-left: 1rem; 
            padding: 5px 5px; 
            transition: background-color 0.15s ease;
        }

        #search-img-button:hover {
            cursor: pointer;
            background-color: #eeeeee;
        }

        .header-right {
            display: flex;
            flex-basis: 25%;
            justify-content: flex-end;
            align-items: center;
        }

        .usericon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .username {
            width: auto;
            height: auto;
            padding: 10px 10px;
        }

        .userbutton {
            display: flex;
            padding: 4px 4px;
            justify-content: flex-end;
            border: 1px solid #cccccce1;
            border-radius: 10px;
            align-items: center;
        }

        .userbutton:hover {
            border: 1px solid #000000;
            cursor: pointer;
        }

        @media screen and (max-width: 1190px) {
            .usericon {
                width: 30px;
                height: 30px;
                border-radius: 50%;
            }
            .username {
                font-size: 14px;
                width: auto;
                height: auto;
                padding: 10px 10px;
            }

            .website-name {
                display: none;
            }

            .search-bar {
                padding: 12px;
                width: 18rem;
                border: 1px solid #cccccce1;
                border-radius: 25px;
                font-size: 16px;
                justify-self: center;
            }
        }

        @media screen and (max-width: 750px) {
            .usericon {
                width: 45px;
                height: 45px;
                border-radius: 50%;
            }

            .username {
                display: none;
            }

            .website-name {
                display: none;
            }

            .search-bar {
                padding: 12px;
                width: 14rem;
                border: 1px solid #cccccce1;
                border-radius: 25px;
                font-size: 16px;
                justify-self: center;
            }
        }
    </style>
</header>