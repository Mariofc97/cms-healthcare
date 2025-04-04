<?php ini_set('display_errors', 1); 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../controllers/contentController.php';

$method = $_SERVER["REQUEST_METHOD"];
switch ($method) {
  case "GET":
    $id = $_GET["diagnosisID"];
    if(!isset($id)){
      throw new Exception("Parameters missing", 400);
    }

    if(empty($id)){
      throw new Exception("Parameters cannnot be empty", 400);
    }

    $controller = new DiagnosisController();
    try{
      $diagnosis = $controller->getById($id);
      $prescriptionController = new PrescriptionController();
      $diagnosis->setPrescriptions($prescriptionController->getByDiagnosis($diagnosis->getId()));
      echo json_encode($diagnosis);
    }catch (Exception $e){
      throw new Exception("Error getting diagnosis.". $e->getMessage(), 500);
    }
    break;
  case "POST":

    break;
  default:
    throw new Exception("$method reqest method is not allowed.", 405);
}





?>