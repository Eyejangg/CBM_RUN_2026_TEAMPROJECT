<?php
session_start();

// If logged in, go to home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
} else {
    // If not logged in, go to register member page as requested
    header("Location: register.php");
}
exit();
?>
