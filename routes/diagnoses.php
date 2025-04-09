<?php
ini_set('display_errors', 1); 

use audit\AuditGenerator; //Import AuditGenerator class from the 'audit' namespace(Audit.php)
use audit\Outcome;  // Import Outcome enum(enumeration) from the 'audit' namespace
use models\Diagnosis;  //Import Diagnosis class from the 'models' namespace(Content.php)

require_once __DIR__ . '/../models/User.php';  // Load User.php using an absolute path based on the current directory
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../controllers/contentController.php';
require_once __DIR__ . '/../models/Audit.php';

$method = $_SERVER["REQUEST_METHOD"];
switch ($method) {
  case "GET":  //get diagnoses
    $id = $_GET["diagnosisID"] ?? null; // Get the 'diagnosisID' parameter from the URL and assign it to $id
    if(!isset($id)){ // Check if the 'id' variable is set
      throw new Exception("Parameters missing", 400); //400 Bad Request, client error response status code
    }
    if(empty($id)){
      throw new Exception("Parameters cannnot be empty", 400);
    }
    $id = filter_var($_GET["diagnosisID"]) ?? null; // Get the 'diagnosisID' parameter from the URL and assign it to $id

    $controller = new DiagnosisController(); //creat new instance (contentController.php)
    try{
      $diagnosis = $controller->getById($id); //call getById function with $id to get diagnosis info matching the diagnosesID
      $prescriptionController = new PrescriptionController(); //creat new instance (contentController.php)
      $diagnosis->setPrescriptions($prescriptionController->getByDiagnosis($diagnosis->getId()));
        // Fetch and set prescriptions for the diagnosis based on the diagnosis' ID
      AuditGenerator::genarateLog("root", "Get Diagnoses", Outcome::SUCCESS);
      echo json_encode($diagnosis);
    }catch (Exception $e){
      AuditGenerator::genarateLog("root", "Get Diagnoses", Outcome::ERROR);
      throw new Exception("Error getting diagnosis.". $e->getMessage(), 500); //500 Internal Server Error, server error response status code
    }
    break;
  case "POST": //adding new diagnosis
    $appointmentid = $_POST["appointmentID"] ?? null;  // Get the 'appointmentID' parameter from the URL and assign it to $appointmentid
    $description = $_POST["description"] ?? null;  // Get the 'description' parameter from the URL and assign it to $description
    if(!isset($appointmentid) || !isset($description)){
      throw new Exception("Parameters missing", 400);  
    }
    if(empty($appointmentid) || empty($description)){
      throw new Exception("Parameters cannnot be empty", 400);
    }
    $appointmentid = filter_var($_POST["appointmentID"], FILTER_SANITIZE_NUMBER_INT) ?? null;  // Get the 'appointmentID' parameter from the URL and assign it to $appointmentid
    $description = htmlspecialchars(strip_tags($_POST["description"])) ?? null;  // Get the 'description' parameter from the URL and assign it to $description

    try{
      $newDiagnosis = new Diagnosis(0, $description, $appointmentid); // Create a new Diagnosis instance with the given values (Content.php)
      $controller = new DiagnosisController();
      $controller->newRecord($newDiagnosis);
      AuditGenerator::genarateLog("root","Adding Diagnosis",outcome::SUCCESS);
    }catch(Exception $e){
      AuditGenerator::genarateLog("root", "Adding Diagnosis", outcome::ERROR);
      throw new Exception("Error adding diagnosis". $e->getMessage(),500);
    }
    break;
  default:
    throw new Exception("$method reqest method is not allowed.", 405);  //405 Method Not Allowed, client error response status code
}





?>