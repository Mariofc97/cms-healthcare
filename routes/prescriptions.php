<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../controllers/contentController.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Prescription;

$requestUri = trim($_SERVER["PATH_INFO"] ?? "", "/");
$requestSegments = explode("/", $requestUri);
$subResource = $requestSegments[1] ?? "";

$controller = new PrescriptionController();

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        switch ($subResource) {
            case "":
                $medicine = htmlspecialchars(strip_tags(trim($_POST["medicine"] ?? "")));
                $dosage = $_POST["dosage"] ?? null;
                $doctorId = $_POST["doctor_id"] ?? null;
                $diagnosisId = $_POST["diagnosis_id"] ?? null;

                if (!$medicine || !$dosage || !$doctorId || !$diagnosisId) {
                    throw new Exception("Missing required fields", 400);
                }

                $prescription = new Prescription(0, $medicine, $dosage);
                $controller->newRecord($prescription, (int)$doctorId, (int)$diagnosisId);
                AuditGenerator::genarateLog("root", "Create prescription", Outcome::SUCCESS);

                http_response_code(200);
                echo json_encode("Prescription created and linked with success");
                break;

            case "update":
                $id = $_POST["id"] ?? null;
                $medicine = $_POST["medicine"] ?? null;
                $dosage = $_POST["dosage"] ?? null;

                if (!$id || !$medicine || !$dosage) {
                    throw new Exception("Missing required fields", 400);
                }

                $prescription = new Prescription((int)$id, $medicine, $dosage);
                $controller->updateRecord($prescription);
                AuditGenerator::genarateLog("root", "Update prescription", Outcome::SUCCESS);

                http_response_code(200);
                echo json_encode("Prescription updated with success");
                break;

            default:
                throw new Exception("Invalid route", 404);
        }
    } else {
        throw new Exception("Invalid request method", 405);
    }
} catch (Exception $e) {
    AuditGenerator::genarateLog("root", "Prescription route error: " . $e->getMessage(), Outcome::ERROR);
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["error" => $e->getMessage()]);
}
