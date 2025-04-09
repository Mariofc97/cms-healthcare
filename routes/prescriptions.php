<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../controllers/contentController.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Prescription;
use models\User;

$requestUri = trim($_SERVER["PATH_INFO"] ?? "", "/");
$requestSegments = explode("/", $requestUri);
$subResource = $requestSegments[1] ?? "";

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if ($_SESSION["userInfo"]["Type"] === User::DOCTOR) {
            $controller = new PrescriptionController();
            switch ($subResource) {
                case "":
                    $medicine = $_POST["medicine"] ?? null;
                    $dosage = $_POST["dosage"] ?? null;
                    $doctorId = $_POST["doctorID"] ?? null;
                    $diagnosisId = $_POST["diagnosisID"] ?? null;

                    if (!isset($medicine, $dosage, $doctorId, $diagnosisId)) {
                        throw new Exception("Missing required fields", 400);
                    }

                    if (empty($medicine) || empty($dosage) || empty($doctorId) || empty($diagnosisId)) {
                        throw new Exception("Required fields cannot be empty", 400);
                    }

                    $medicine = htmlspecialchars(strip_tags($medicine));
                    $dosage = htmlspecialchars(strip_tags($dosage));
                    $doctorId = htmlspecialchars(strip_tags($doctorId));
                    if (!filter_var($doctorId, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid DoctorID");
                    }
                    $diagnosisId = htmlspecialchars(strip_tags($diagnosisId));
                    if (!filter_var($diagnosisId, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid DiagnosisID");
                    }

                    $prescription = new Prescription(0, $medicine, $dosage);
                    $controller->newRecord($prescription, (int)$doctorId, (int)$diagnosisId);
                    AuditGenerator::genarateLog("root", "Create prescription", Outcome::SUCCESS);

                    if (isset($_FILES["dataFile"])) {
                        try {
                            $conditionController = new ConditionController();
                            $patientController = new PatientController();

                            $condition = $conditionController->getByDiagnosis((int)$diagnosisId);
                            $patient = $patientController->getByCondition($condition->getId());

                            $targetDir = __DIR__ . "/../data/" . $patient->getEmail() . "/prescriptions";
                            if (!file_exists($targetDir)) {
                                mkdir($targetDir, 0777, true);
                            }
                            $targetDir .= "/" . strtolower(basename($_FILES["dataFile"]["name"]));
                            if (move_uploaded_file($_FILES["dataFile"]["tmp_name"], $targetDir)) {
                                AuditGenerator::genarateLog("root", "Upload file", Outcome::SUCCESS);
                                echo json_encode(basename($_FILES["dataFile"]["name"]) . " has been uploaded.");
                            } else {
                                throw new Exception("Not possible to upload the file.", 500);
                            }
                        } catch (Exception $e) {
                            echo json_encode("Error uploading file: " . $e->getMessage());
                            AuditGenerator::genarateLog("root", "Upload file", Outcome::ERROR);
                        }
                    }

                    echo json_encode("Prescription created with success");
                    break;

                case "update":
                    $id = $_POST["id"] ?? null;
                    $medicine = $_POST["medicine"] ?? null;
                    $dosage = $_POST["dosage"] ?? null;

                    if (!isset($id)) {
                        throw new Exception("Missing required fields", 400);
                    }

                    if (empty($id)) {
                        throw new Exception("Required fields cannot be empty", 400);
                    }

                    $id = htmlspecialchars(strip_tags($id));
                    if (!filter_var($id, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid prescription ID", 400);
                    }

                    $existing = $controller->getById((int)$id);

                    if ($medicine) {
                        $medicine = htmlspecialchars(strip_tags($medicine));
                        $existing->setMedicine($medicine);
                    }

                    if ($dosage) {
                        $dosage = htmlspecialchars(strip_tags($dosage));
                        $existing->setDosage($dosage);
                    }
                    $controller->updateRecord($existing);

                    AuditGenerator::genarateLog("root", "Update prescription", Outcome::SUCCESS);
                    echo json_encode("Prescription updated with success");
                    break;

                default:
                    throw new Exception("Invalid route", 404);
            }
        } else {
            throw new Exception("User not allowed", 403);
        }
    } else {
        throw new Exception("Invalid request method", 405);
    }
} catch (Exception $e) {
    AuditGenerator::genarateLog("root", "Prescription management" . $e->getMessage(), Outcome::ERROR);
    throw new Exception("There was an error managing prescriptions: " . $e->getMessage(), $e->getCode());
}
