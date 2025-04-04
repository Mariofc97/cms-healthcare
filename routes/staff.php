<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/contentController.php';

use models\Staff;
use models\User;

 ini_set('display_errors', 1); 

$method = $_SERVER["REQUEST_METHOD"];
switch ($method) {
  case "POST":
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $password = $_POST["pass"];

    if (!isset($fname) || !isset($lname) || !isset($phone) || !isset($email) || !isset($password)){
      throw new Exception("Parameters missing", 400);
    }

    if (empty($fname) || empty($lname) || empty($phone) || empty($email) || empty($password)){
      throw new Exception("Parameters cannnot be empty", 400);
    }

    $newStaff = new Staff(0, $fname, $lname, $phone, $email, $password, User::STAFF);
    $controller = new UserController();

    $controller->newRecord($newStaff);
    break;
  default:
    throw new Exception("$method reqest method is not allowed.", 405);
}

?>


<!-- Add new members -->
<form method="post" enctype="multipart/form-data">
  <input type="text" name="fname" placeholder="First Name">
  <input type="text" name="lname" placeholder="Lasr Name">
  <input type="number" name="phone" placeholder="Phone">
  <input type="email" name="email" placeholder="Email">
  <input type="text" name="pass" placeholder="Password">
  <input type="text" name="type" placeholder="Type">
  <input type="submit" name="submit">
</form>
