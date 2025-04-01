<?php
session_start();

use authentication as auth;

$users = auth\getAllUsers();

$email = $_POST['email'] ?? '';
$email = $_POST['password'] ?? '';

// ! FOR NOW CAN BE LIKE THIS, BUT AFTER THE DATABASE, THINGS WILL CHANGE
foreach ($users as $user) {
    if ($user->checkCredentials($email, $password)) {
        $_SESSION['user'] = [
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ];
        header('Location: ../views/dashboard.php');
        exit();
    }
}
echo "Invalid credentials";
