<?php
session_start();
if(!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

echo "Welcome, " . $_SESSION['user']['email'] . " (" . $_SESSION['user']['role'] . ")";
?>