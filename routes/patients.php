<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Condition;
use models\Gender;
use models\MedicalRecord;
use models\Patient;
use models\Symptom;

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$subResource = @strtolower($requestSegments[1]) ?? "";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        switch ($subResource) {
            case "medical-record":
                $id = $_GET["patientID"];
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $patientController = new PatientController();
                $conditionsController = new ConditionController();
                try {
                    $patient = $patientController->getById((int)$id);
                    $patientConditions = $conditionsController->getByPatient($patient->getId());
                    foreach ($patientConditions as $condition) {
                        $diagnosisController = new DiagnosisController();
                        $condition->setDiagnoses($diagnosisController->getByCondition($condition->getId()));
                        foreach ($condition->getDiagnoses() as $diagnosis) {
                            $prescriptionController = new PrescriptionController();
                            $diagnosis->setPrescriptions($prescriptionController->getByDiagnosis($diagnosis->getId()));
                        }
                    }

                    $medicalRecord = new MedicalRecord($patient, $patientConditions);
                    echo json_encode($medicalRecord);
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Get Medical Record", Outcome::ERROR);
                    throw new Exception("Error getting patient's medical record: " . $e->getMessage(), 500);
                }
                break;
            case "condition":
                $id = $_GET["patientID"];
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $conditionController = new ConditionController();
                try {
                    $conditions = $conditionController->getByPatient((int)$id);
                    $conditionsNum = count($conditions);
                    if ($conditionsNum !== 0) {
                        $latestCondition = $conditions[$conditionsNum - 1];
                        $diagnosisController = new DiagnosisController();
                        $latestCondition->setDiagnoses($diagnosisController->getByCondition($latestCondition->getId()));
                        echo json_encode($latestCondition);
                    } else {
                        echo json_encode("Patient doesn't have any conditions");
                    }
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Get patient latest condition", Outcome::ERROR);
                    throw new Exception("Error getting patient's latest condition: " . $e->getMessage(), 500);
                }
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
                try {
                    $appointments = $controller->getByPatient($id);
                    echo json_encode($appointments);
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Get Appointments", Outcome::ERROR);
                    throw new Exception("Error getting patient appointments: " . $e->getMessage(), 500);
                }
                break;
            default:
                throw new Exception("Invalid resource patients/$subResource", 404);
        }
        break;
    case "POST":
        $id = $_POST["patientID"] ?? null;
        $fname = $_POST["fname"] ?? null;
        $lname = $_POST["lname"] ?? null;
        $phone = $_POST["phone"] ?? null;
        $email = $_POST["email"] ?? null;
        $pass = $_POST["password"] ?? null;
        $gender = $_POST["gender"] ?? null;
        $birthdate = $_POST["birthdate"] ?? null;
        $address = $_POST["address"] ?? null;

        switch ($subResource) {
            case "":
                if (!isset($phone)) {
                    $phone = "No Phone";
                }
                if (!isset($address)) {
                    $address = "No Address";
                }

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
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $controller = new PatientController();
                try {
                    $patient = $controller->getById((int)$id);
                    if (isset($fname) && !empty($fname)) {
                        $patient->setFname($fname);
                    }
                    if (isset($lname) && !empty($lname)) {
                        $patient->setLname($lname);
                    }
                    if (isset($phone) && !empty($phone)) {
                        $patient->setPhone($phone);
                    }
                    if (isset($email) && !empty($email)) {
                        $patient->setEmail($email);
                    }
                    if (isset($pass) && !empty($pass)) {
                        $patient->setPassword($pass);
                    }
                    if (isset($gender) && !empty($gender)) {
                        if ($gender === "M") {
                            $gender = Gender::MALE;
                        } elseif ($gender === "F") {
                            $gender = Gender::FEMALE;
                        } else {
                            throw new InvalidArgumentException("Invalid gender", 400);
                        }
                        $patient->setGender($gender);
                    }
                    if (isset($birthdate) && !empty($birthdate)) {
                        $birthdate = new DateTime($birthdate);
                        $patient->setBirth($birthdate);
                    }
                    if (isset($address) && !empty($address)) {
                        $patient->setAddress($address);
                    }

                    $controller->updateRecord($patient);
                    AuditGenerator::genarateLog("root", "Update patient", Outcome::SUCCESS);
                    echo json_encode("Patient updated successfully");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Update patient", Outcome::ERROR);
                    throw new Exception("Error updating patient: " . $e->getMessage(), 500);
                }
                break;
            case "delete":
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $controller = new PatientController();
                try {
                    $patient = $controller->getById((int)$id);
                    $controller->deleteRecord((int)$id);
                    AuditGenerator::genarateLog("root", "Delete patient", Outcome::SUCCESS);
                    echo json_encode("Patient deleted successfully");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Delete patient", Outcome::ERROR);
                    throw new Exception("Error deleting patient: " . $e->getMessage(), 500);
                }
                break;
            case "condition":
                $patientId = $_POST["patientID"] ?? null;
                $symptoms = $_POST["symptoms"] ?? [];

                if (!isset($patientId)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($patientId)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $symptomsArr = [];
                foreach ($symptoms as $symptom) {
                    $symptomsArr[] = new Symptom(0, $symptom);
                }

                $controller = new ConditionController();
                $condition = new Condition(0, new DateTime(), (int)$patientId, $symptomsArr);
                try {
                    $controller->newRecord($condition);
                    AuditGenerator::genarateLog("root", "Create condition", Outcome::SUCCESS);
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Create condition", Outcome::ERROR);
                    throw new Exception("Error creating condition: " . $e->getMessage(), 500);
                }
                break;
            default:
                throw new Exception("Invalid resource patients/$subResource", 404);
        }
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
