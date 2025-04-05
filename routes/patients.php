<?php

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;

use models\Gender;
use models\Patient;


$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$subResource = @strtolower($requestSegments[1]) ?? "";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        switch ($subResource) {
            case "medical-record":
                break;
            case "condition":
                break;
            case "appointments":
                $id = $_GET["patientID"];
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $controller = new AppointmentController();
                $appointments = $controller->getByPatient($id);
                echo json_encode($appointments);
                break;
            default:
                throw new Exception("Invalid resource patients/$subResource", 404);
        }
        break;
    case "POST":
        switch ($subResource) {
            case "":
                $fname = $_POST["fname"];
                $lname = $_POST["lname"];
                $phone = $_POST["phone"] ?? "No Phone";
                $email = $_POST["email"];
                $pass = $_POST["password"];
                $gender = $_POST["gender"];
                $birthdate = $_POST["birthdate"];
                $address = $_POST["address"] ?? "No Address";

                if (!isset($_POST["fname"], $_POST["lname"], $_POST["email"], $_POST["password"], $_POST["gender"], $_POST["birthdate"])) {
                    throw new Exception("Parameters missing", 400);
                }

                if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($gender) || empty($birthdate)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                if ($gender === "M") {
                    $gender = Gender::MALE;
                } elseif ($gender === "F") {
                    $gender = Gender::FEMALE;
                } else {
                    throw new InvalidArgumentException("Invalid gender ", 400);
                }

                $birthdate = new DateTime($birthdate);

                $newPatient = new Patient(0, $lname, $fname, $phone, $email, $pass, $gender, $birthdate, $address);
                $controller = new PatientController();

                try {
                    $controller->newRecord($newPatient);
                    AuditGenerator::genarateLog("root", "Create patient", Outcome::SUCCESS);
                    echo "Patient created successfully";
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Create patient", Outcome::ERROR);
                    throw new Exception("Error creating new patient: " . $e->getMessage(), 500);
                }
                break;
            case "update":
                break;
            case "delete":
                break;
            default:
                throw new Exception("Invalid resource patients/$subResource", 404);
        }
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
