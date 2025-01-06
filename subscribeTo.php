<?php 
session_start();

if ($_SERVER['REQUEST_METHOD'] = "POST") {
    if (isset($_POST['subto'])) {
        $subscribee = strval($_POST['subto']);

        include 'connect.php';
        if ($connect->query("UPDATE users SET subscribers = subscribers + 1 WHERE id='$subscribee'")) {

            $subbedTo = json_decode($_SESSION['currentUser']['subscribed_to']);
            $subbedTo[] = array("id" => $subscribee);
            $subbedTo = json_encode($subbedTo);

            $_SESSION['currentUser']['subscribed_to'] = $subbedTo;
            $connect->query("UPDATE users SET subscribed_to = '$subbedTo' WHERE id=" . $_SESSION['currentUser']['id']);

            echo $connect->query("SELECT subscribers FROM users WHERE id='$subscribee'")->fetch_assoc()['subscribers'];
        }
        else {
            echo -1;
        }
    }  
    else if (isset($_POST['unsubto'])) {
        $subscribee = strval($_POST['unsubto']);

        include 'connect.php';
        if ($connect->query("UPDATE users SET subscribers = subscribers - 1 WHERE id='$subscribee'")) {
            $subbedTo = json_decode($_SESSION['currentUser']['subscribed_to'], true);

            foreach ($subbedTo as $i => $subscription) {
                if ($subscription['id'] == $subscribee) {
                    unset($subbedTo[$i]);
                    break;
                }
            }
            
            $subbedTo = json_encode(array_values($subbedTo));
            $_SESSION['currentUser']['subscribed_to'] = $subbedTo;
            $connect->query("UPDATE users SET subscribed_to = '$subbedTo' WHERE id=" . $_SESSION['currentUser']['id']);

            echo $connect->query("SELECT subscribers FROM users WHERE id='$subscribee'")->fetch_assoc()['subscribers'];
        }
        else {
            echo -1;
        }
    }
}
?>