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


switch ($method) {
    case "GET";
        $id = $_GET["doctorID"] ?? null;

        try {
            if (!$id) {
                throw new Exception("Missing doctorID", 400);
            }

            $id = htmlspecialchars(strip_tags($id));

            if (!filter_var($id, FILTER_VALIDATE_INT)) {
                throw new Exception("Invalid doctor ID", 400);
            }

            $controller = new DoctorController();
            $doctor = $controller->getById((int)$id);
            echo json_encode($doctor);
        } catch (Exception $e) {
            AuditGenerator::genarateLog("root", "Get doctor", Outcome::ERROR);
            throw new Exception("Error getting doctor: " . $e->getMessage(), $e->getCode());
        }
        break;

    case "POST":
        $id = $_POST["doctorID"] ?? null;
        $fname = $_POST["fname"] ?? null;
        $lname = $_POST["lname"] ?? null;
        $phone = $_POST["phone"] ?? "No Phone";
        $email = $_POST["email"] ?? null;
        $pass = $_POST["password"] ?? null;
        $specialty = $_POST["specialty"] ?? null;

        switch ($subResource) {
            case "":
                try {
                    if (!isset($fname) || !isset($lname) || !isset($email) || !isset($pass) || !isset($specialty)) {
                        throw new Exception("Missing required fields", 400);
                    }

                    if (empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($specialty)) {
                        throw new Exception("Required fields cannot be empty", 400);
                    }

                    $controller = new DoctorController();

                    if ($controller->doctorExistsByEmail($email)) {
                        throw new Exception("Some error occured", 409);
                    }

                    $fname = htmlspecialchars(strip_tags($fname));
                    $lname = htmlspecialchars(strip_tags($lname));
                    $phone = htmlspecialchars(strip_tags($phone));
                    $email = htmlspecialchars(strip_tags($email));
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Invalid email", 400);
                    }
                    $pass = strip_tags($pass);
                    $specialty = htmlspecialchars(strip_tags($specialty));

                    $hashedPass = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 10]);

                    $newDoctor = new Doctor(0, $fname, $lname, $phone, $email, $hashedPass, $specialty);
                    $controller->newRecord($newDoctor);

                    AuditGenerator::genarateLog("root", "Create doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor created with success", 200);
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Create doctor", Outcome::ERROR);
                    throw new Exception("Error creating doctor: " . $e->getMessage(), $e->getCode());
                }
                break;
            case "update":
                try {
                    if (!isset($id)) {
                        throw new Exception("Missing doctorID", 400);
                    }

                    $id = htmlspecialchars(strip_tags($id));
                    if (!filter_var($id, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid doctor ID", 400);
                    }

                    $controller = new DoctorController();
                    $existing = $controller->getById((int)$id);
                    if (!$existing) {
                        throw new Exception("Doctor not found with ID $id", 404);
                    }

                    if (!$fname && !$lname && !$phone && !$email && !$pass && !$specialty) {
                        throw new Exception("No fiels provided to update");
                    }

                    if ($fname) {
                        $fname = htmlspecialchars(strip_tags($fname));
                        $existing->setFname($fname);
                    }
                    if ($lname) {
                        $lname = htmlspecialchars(strip_tags($lname));
                        $existing->setLname($lname);
                    }
                    if ($phone) {
                        $phone = htmlspecialchars(strip_tags($phone));
                        $existing->setPhone($phone);
                    }
                    if ($email) {
                        $email = htmlspecialchars(strip_tags($email));
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception("Invalid email", 400);
                        }
                        $existing->setEmail($email);
                    }
                    if ($pass) {
                        $pass = strip_tags($pass);
                        $pass = password_hash($pass, PASSWORD_BCRYPT, ["cost" => 10]);
                        $existing->setPassword($pass);
                    }
                    if ($specialty) {
                        $specialty = htmlspecialchars(strip_tags($specialty));
                        $existing->setSpecialty($specialty);
                    }
                    $controller->updateRecord($existing);

                    AuditGenerator::genarateLog("root", "Update doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor update with success");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Update doctor", Outcome::ERROR);
                    throw new Exception("Error updating doctor: " . $e->getMessage(), $e->getCode());
                }
                break;
            case "delete":
                $id = $_POST["doctorID"] ?? null;
                try {
                    if (!$id) {
                        http_response_code(400);
                        throw new Exception("Missing doctorID", 400);
                    }

                    $id = htmlspecialchars(strip_tags($id));
                    if (!filter_var($id, FILTER_VALIDATE_INT)) {
                        throw new Exception("Invalid doctor ID", 400);
                    }

                    $controller = new DoctorController();

                    $existing = $controller->getById((int)$id);
                    if (!$existing) {
                        http_response_code(404);
                        throw new Exception("Doctor not found with ID $id", 500);
                    }

                    $controller->deleteRecord((int)$id);
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor deleted with success");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::ERROR);
                    throw new Exception("Error deleting doctor: " . $e->getMessage(), $e->getCode());
                }
                break;
            default:
                throw new Exception("Invalid resource doctors/$subResource", 404);
        }
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
