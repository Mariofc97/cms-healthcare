<?php
session_start();
$users = require '../includes/users.php';

$email = $_POST['email'] ?? '';
$email = $_POST['password'] ?? '';

foreach ($users as $user) {
    if ($user->checkCredentials($email, $password)) {
        $_SESSION['user'] = [
            'email' => $user->email,
            'role' => $user->role
        ];
        header('Location: ../views/dashboard.php');
        exit();
    }
}
echo "Invalid credentials";