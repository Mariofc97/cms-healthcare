<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../controllers/contentController.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Doctor;

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$subResource = $requestSegments[1] ?? "";

$method = $_SERVER["REQUEST_METHOD"];

$controller = new DoctorController();

switch ($method) {
    case "GET";
        $id = $_GET["doctorID"] ?? null;

        try {
            if (!$id) {
                throw new Exception("Missing doctorID", 400);
            }

            $doctor = $controller->getById((int)$id);
            AuditGenerator::genarateLog("root", "Get doctor", Outcome::SUCCESS);

            echo json_encode($doctor);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            AuditGenerator::genarateLog("root", "Get doctor", Outcome::ERROR);
            echo json_encode(["error" => "Error getting doctor: ." . $e->getMessage()]);
        }
        break;

    case "POST":
        $id = $_POST["doctorID"] ?? null;
        $fname = $_POST["fname"] ?? null;
        $lname = $_POST["lname"] ?? null;
        $phone = $_POST["phone"] ?? null;
        $email = $_POST["email"] ?? null;
        $pass = $_POST["password"] ?? null;
        $specialty = $_POST["specialty"] ?? null;

        switch ($subResource) {
            case "":
                try {
                    if (!isset($fname) || !isset($lname) || !isset($email) || !isset($pass) || !isset($specialty)) {
                        echo $fname, $lname, $email, $pass, $specialty;
                        throw new Exception("Missing required fields", 400);
                    }

                    if ($controller->doctorExistsByEmail($email)) {
                        throw new Exception("Some error occured", 409);
                    }

                    $newDoctor = new Doctor(0, $fname, $lname, $phone, $email, $pass, $specialty);
                    $controller->newRecord($newDoctor);

                    AuditGenerator::genarateLog("root", "Create doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor created with success", 200);
                } catch (Exception $e) {
                    http_response_code($e->getCode() ?: 500);
                    AuditGenerator::genarateLog("root", "Create doctor", Outcome::ERROR);
                    echo json_encode(["error" => "Error creating doctor: ." . $e->getMessage()]);
                }
                break;
            case "update":
                try {
                    if (!$id) {
                        throw new Exception("Missing doctorID", 400);
                    }

                    $existing = $controller->getById((int)$id);
                    if (!$existing) {
                        throw new Exception("Doctor not found with ID $id", 404);
                    }

                    if (!$fname && !$lname && !$phone && !$email && !$pass && !$specialty) {
                        throw new Exception("No fiels provided to update");
                    }

                    if ($fname) $existing->setFname($fname);
                    if ($lname) $existing->setLname($lname);
                    if ($phone) $existing->setPhone($phone);
                    if ($email) $existing->setEmail($email);
                    if ($pass) $existing->setPassword($pass);
                    if ($specialty) $existing->setSpecialty($specialty);

                    $controller->updateRecord($existing);

                    http_response_code(200);
                    AuditGenerator::genarateLog("root", "Update doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor uddate with success");
                } catch (Exception $e) {
                    http_response_code($e->getCode() ?: 500);
                    AuditGenerator::genarateLog("root", "Update doctor", Outcome::ERROR);
                    echo json_encode(["error" => "Error updating doctor: " . $e->getMessage()]);
                }
                break;
            case "delete":
                $id = $_POST["doctorID"] ?? null;
                try {
                    if (!$id) {
                        http_response_code(400);
                        throw new Exception("Missing doctorID");
                    }

                    $existing = $controller->getById((int)$id);
                    if (!$existing) {
                        http_response_code(404);
                        throw new Exception("Doctor not found with ID $id");
                    }

                    $controller->deleteRecord((int)$id);
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor deleted with success");
                } catch (Exception $e) {
                    http_response_code($e->getCode() ?: 500);
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::ERROR);
                    echo json_encode(["error" => "Error deleting doctor: " . $e->getMessage()]);
                }
                break;
            default:
                throw new Exception("Invalid resource doctors/$subResource", 404);
        }
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
