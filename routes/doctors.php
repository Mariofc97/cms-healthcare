<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../controllers/contentController.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Doctor;

$requestUri = isset($_SERVER["PATH_INFO"]) ? trim($_SERVER["PATH_INFO"], "/") : "";

$requestSegments = explode("/",$requestUri);
$subResource = $requestSegments[1] ?? "";

$method = $_SERVER["REQUEST_METHOD"];

$controller = new DoctorController();

switch ($method) {
    case "GET";
        $id = $_GET["doctorID"] ?? null;

        try {
            if (!$id) {
                throw new Exception ("Missing doctorID", 400);
            }

            $doctor = $controller->getById((int)$id);
            AuditGenerator::genarateLog("root","Get doctor", Outcome::SUCCESS);

            echo json_encode($doctor);
        } catch (Exception $e) {
            AuditGenerator::genarateLog("root","Get doctor", Outcome::ERROR);

            throw new Exception ("Error getting doctor: " .$e->getMessage(),500);
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

        switch($subResource) {
            case "":
                try {
                    if(!$fname || !$lname || !$phone || !$pass || !$specialty) {
                        throw new Exception ("Missing required fields", 400);
                    }
        
                    $newDoctor = new Doctor(0, $fname, $lname, $phone, $email, $pass, $specialty);
                    $controller->newRecord($newDoctor);
        
                    AuditGenerator::genarateLog("root","Create doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor created with success");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root","Create doctor",Outcome::ERROR);
                    throw new Exception ("Error creating doctor: " . $e->getMessage(),500);
                }
                break;
            case "update":
                try {
                    if(!$id) {
                        throw new Exception("Missing doctorID",400);
                    }

                    $existing = $controller->getById((int)$id);
                    if ($fname) $existing->setFname($fname);
                    if ($lname) $existing->setLname($lname);
                    if ($phone) $existing->setPhone($phone);
                    if ($email) $existing->setEmail($email);
                    if ($pass) $existing->setPassword($pass);
                    if($specialty) $existing->setSpecialty($specialty);
                    
                    $controller->updateRecord($existing);
                    AuditGenerator::genarateLog("root", "Update doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor uddate with success");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root","Update doctor", Outcome::ERROR);
                    throw new Exception ("Error updating doctor: " . $e->getMessage(),500);
                }
                break;
            case "delete":
                try {
                    if (!$id) {
                        throw new Exception("Missing doctorID",400);
                    }

                    $controller->deleteRecord((int)$id);
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::SUCCESS);
                    echo json_encode("Doctor deleted with success");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Delete doctor", Outcome::ERROR);
                    throw new Exception("Error deleting doctor: " . $e->getMessage(), 500);
                }
                break;
            default:
                throw new Exception("Invalid resource doctors/$subResource",404);
        }
        break;
        default:
        throw new Exception("$method request method is not allowed",405);
}


