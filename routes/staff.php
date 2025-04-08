<?php
ini_set('display_errors', 1);

use audit\AuditGenerator; //Import AuditGenerator class from the 'audit' namespace(Audit.php)
use audit\Outcome; // Import Outcome enum(enumeration) from the 'audit' namespace
use models\Staff; //Import Staff class from the 'models' namespace(User.php)
use models\User; //Import User class from the 'models' namespace(User.php)

require_once __DIR__ . '/../models/User.php';  // Load User.php using an absolute path based on the current directory
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../controllers/contentController.php';

$method = $_SERVER["REQUEST_METHOD"];
$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";
//If PATH_INFO exists, retrieve the string with the leading and trailing slashes removed; otherwise, use an empty string
$path = explode("/", $requestUri);

switch ($method) {
  case "POST":
    $fname = htmlspecialchars(strip_tags($_POST["fname"])) ?? null;// Get the 'fname' parameter from the URL, sanitize and assign it to $fname
    $lname = htmlspecialchars(strip_tags($_POST["lname"])) ?? null;
    $phone = htmlspecialchars(strip_tags($_POST["phone"])) ?? null;
    $email = htmlspecialchars(strip_tags($_POST["email"])) ?? null;
    $password = password_hash(strip_tags($_POST["pass"]),PASSWORD_DEFAULT,['cost'=>10]) ?? null; //Sanitize and convert the password into a hashed value
    
    //update staff member's info, delete staff
    if(in_array("update", $path) || in_array("delete", $path) ){ 
      $id = filter_var($_POST["id"]) ?? null;
      if(!isset($id) || empty($id)){
        throw new Exception("User ID is missing", 400);
      }
      $controller = new StaffController();
      if (in_array("update", $path)) {  //update staff info
        try {
          $updateStaff = $controller->getById($id); //contentController.php > StaffControlloer
          if (isset($fname) && !empty($fname)) {
            $updateStaff->setFname($fname);  //User.php > class Staff
          }
          if (isset($lname) && !empty($lname)) {
            $updateStaff->setLname($lname);
          }
          if (isset($phone) && !empty($phone)) {
            $updateStaff->setPhone($phone);
          }
          if (isset($email) && !empty($email)) {
            $updateStaff->setEmail($email);
          }
          if (isset($password) && !empty($password)) {
            $updateStaff->setPassword($password);
          }
          $controller->updateRecord($updateStaff);
          AuditGenerator::genarateLog("root", "Update Staff", Outcome::SUCCESS);
          echo json_encode("Staff updated successfully");
        } catch (Exception $e) {
          AuditGenerator::genarateLog("root", "Update Staff", Outcome::ERROR);
          throw new Exception("Error getting staff information." . $e->getMessage(), 500); //500 Internal Server Error, server error response status code
        }
      } elseif (in_array("delete", $path)) {  //delete(deactivate) staff
        try {
          $controller->deleteStaff($id);
          AuditGenerator::genarateLog("root", "Delete Staff", Outcome::SUCCESS);
          echo json_encode("Staff deleted successfully");
        } catch (Exception $e) {
          AuditGenerator::genarateLog("root", "Delete Staff", Outcome::ERROR);
          throw new Exception("Error getting staff information." . $e->getMessage(), 500);
        }
      }

      //add new staff member's to the system
    } else {
      if (!isset($fname) || !isset($lname) || !isset($phone) || !isset($email) || !isset($password)) {
        throw new Exception("Parameters missing", 400); //400 Bad Request, client error response status code
      }
      if (empty($fname) || empty($lname) || empty($phone) || empty($email) || empty($password)) {
        throw new Exception("Parameters cannnot be empty", 400);
      }

      $newStaff = new Staff(0, $fname, $lname, $phone, $email, $password, User::STAFF);
      try {
        $controller = new StaffController();
        $controller->newRecord($newStaff);
        AuditGenerator::genarateLog("root", "Delete Staff", outcome::SUCCESS);
      } catch (Exception $e) {
        AuditGenerator::genarateLog("root", "Delete Staff", outcome::ERROR);
        throw new Exception("Error deleting staff." . $e->getMessage(), 500);
      }
    }
    break;
  default:
    throw new Exception("$method reqest method is not allowed.", 405); //405 Method Not Allowed, client error response status code
}
