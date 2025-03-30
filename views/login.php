<?php

if(isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!-- html of the Login Form -->
<h2>Login Form</h2>
<form method= "POST" action="controllers/auth.php">
    <input type="email" name="email"required placeholder="Email">
    <input type="password" name="password"required placeholder="Password">
    <button type="submit">Login</button>
</form>
