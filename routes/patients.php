<?php

declare(strict_types=1);
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Condition;
use models\DetailedAppointment;
use models\Gender;
use models\MedicalRecord;
use models\Patient;
use models\Symptom;
use models\User;

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$subResource = $requestSegments[1] ?? "";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        $id = $_GET["patientID"] ?? null;

        if (!isset($id)) {
            throw new Exception("Parameters missing", 400);
        }
        if (empty($id)) {
            throw new Exception("Parameters cannot be empty", 400);
        }

        $id = htmlspecialchars(strip_tags($id));
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            throw new Exception("Invalid patient ID", 400);
        }

        switch ($subResource) {
            case "medical-record":
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT || $_SESSION["userInfo"]["Type"] === User::DOCTOR) {
                    try {
                        $patientController = new PatientController();
                        $conditionsController = new ConditionController();
                        $patient = $patientController->getById((int)$id);
                        $patientConditions = $conditionsController->getByPatient((int)$patient->getId());
                        foreach ($patientConditions as $condition) {
                            $diagnosisController = new DiagnosisController();
                            $condition->setDiagnoses($diagnosisController->getByCondition((int)$condition->getId()));
                            foreach ($condition->getDiagnoses() as $diagnosis) {
                                $prescriptionController = new PrescriptionController();
                                $diagnosis->setPrescriptions($prescriptionController->getByDiagnosis((int)$diagnosis->getId()));
                            }
                        }

                        $medicalRecord = new MedicalRecord($patient, $patientConditions);
                        echo json_encode($medicalRecord);
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Get Medical Record", Outcome::ERROR);
                        throw new Exception("Error getting patient's medical record: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            case "condition":
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT || $_SESSION["userInfo"]["Type"] === User::DOCTOR) {
                    try {
                        $conditionController = new ConditionController();
                        $conditions = $conditionController->getByPatient((int)$id);
                        $conditionsNum = count($conditions);
                        if ($conditionsNum !== 0) {
                            $latestCondition = $conditions[$conditionsNum - 1];
                            $diagnosisController = new DiagnosisController();
                            $latestCondition->setDiagnoses($diagnosisController->getByCondition((int)$latestCondition->getId()));
                            echo json_encode($latestCondition);
                        } else {
                            echo json_encode("Patient doesn't have any conditions");
                        }
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Get patient latest condition", Outcome::ERROR);
                        throw new Exception("Error getting patient's latest condition: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            case "appointments":
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT) {
                    try {
                        $controller = new AppointmentController();
                        $patientController = new PatientController();
                        $patientController->getById((int)$id);
                        $appointments = $controller->getByPatient((int)$id);

                        $detailedAppointments = [];
                        foreach ($appointments as $appointment) {
                            $doctor = $controller->getDoctorInfo($appointment->getId());
                            $detailedAppointments[] = new DetailedAppointment($doctor, $appointment);
                        }

                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Get Appointments", Outcome::SUCCESS);
                        echo json_encode($detailedAppointments);
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Get Appointments", Outcome::ERROR);
                        throw new Exception("Error getting patient appointments: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
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
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT || $_SESSION["userInfo"]["Type"] === User::STAFF) {
                    if (!isset($phone) || empty($phone)) {
                        $phone = "No Phone";
                    }
                    if (!isset($address) || empty($address)) {
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

                    $fname = htmlspecialchars(strip_tags($fname));
                    $lname = htmlspecialchars(strip_tags($lname));
                    $phone = htmlspecialchars(strip_tags($phone));
                    $email = htmlspecialchars(strip_tags($email));
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Invalid email", 400);
                    }
                    $pass = strip_tags($pass);
                    $birthdate = htmlspecialchars(strip_tags($birthdate));
                    $address = htmlspecialchars(strip_tags($address));

                    try {
                        $birthdate = new DateTime($birthdate);

                        $newPatient = new Patient(0, $lname, $fname, $phone, $email, $pass, $gender, $birthdate, $address);

                        $controller = new PatientController();
                        if ($controller->patientExistsByEmail($email)) {
                            throw new Exception("Not possible to add patient", 409);
                        }
                        $controller->newRecord($newPatient);
                        AuditGenerator::genarateLog("root", "Create patient", Outcome::SUCCESS);
                        echo json_encode("Patient created successfully");
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog("root", "Create patient", Outcome::ERROR);
                        throw new Exception("Error creating new patient: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            case "update":
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT || $_SESSION["userInfo"]["Type"] === User::STAFF) {
                    if (!isset($id)) {
                        throw new Exception("Parameters missing", 400);
                    }
                    if (empty($id)) {
                        throw new Exception("Parameters cannot be empty", 400);
                    }

                    $id = htmlspecialchars(strip_tags($id));
                    if (!filter_var($id, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid patient ID", 400);
                    }

                    try {
                        $controller = new PatientController();
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
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Update patient", Outcome::SUCCESS);
                        echo json_encode("Patient updated successfully");
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Update patient", Outcome::ERROR);
                        throw new Exception("Error updating patient: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            case "delete":
                if ($_SESSION["userInfo"]["Type"] === User::STAFF) {
                    if (!isset($id)) {
                        throw new Exception("Parameters missing", 400);
                    }
                    if (empty($id)) {
                        throw new Exception("Parameters cannot be empty", 400);
                    }

                    $id = htmlspecialchars(strip_tags($id));
                    if (!filter_var($id, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid patient ID", 400);
                    }

                    try {
                        $controller = new PatientController();
                        $patient = $controller->getById((int)$id);
                        $controller->deleteRecord((int)$id);
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Delete patient", Outcome::SUCCESS);
                        echo json_encode("Patient deleted successfully");
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Delete patient", Outcome::ERROR);
                        throw new Exception("Error deleting patient: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            case "condition":
                if ($_SESSION["userInfo"]["Type"] === User::PATIENT || $_SESSION["userInfo"]["Type"] === User::DOCTOR) {

                    $patientId = $_POST["patientID"] ?? null;
                    $symptoms = $_POST["symptoms"] ?? [];

                    if (!isset($patientId)) {
                        throw new Exception("Parameters missing", 400);
                    }
                    if (empty($patientId)) {
                        throw new Exception("Parameters cannot be empty", 400);
                    }

                    $patientId = htmlspecialchars(strip_tags($patientId));
                    if (!filter_var($patientId, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid patientID");
                    }

                    $symptomsArr = [];
                    foreach ($symptoms as $symptom) {
                        $symptom = htmlspecialchars(strip_tags($symptom));
                        $symptomsArr[] = new Symptom(0, $symptom);
                    }

                    try {
                        $controller = new ConditionController();
                        $condition = new Condition(0, new DateTime(), (int)$patientId, $symptomsArr);
                        $controller->newRecord($condition);
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Create condition", Outcome::SUCCESS);
                        echo json_encode("Condition created successfully");
                    } catch (Exception $e) {
                        AuditGenerator::genarateLog($_SESSION["userInfo"]["Email"], "Create condition", Outcome::ERROR);
                        throw new Exception("Error creating condition: " . $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw new Exception("User not allowed", 403);
                }
                break;
            default:
                throw new Exception("Invalid resource patients/$subResource", 404);
        }
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
