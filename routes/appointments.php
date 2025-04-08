<?php
session_start();

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Appointment;
use models\User;

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$subResource = $requestSegments[1] ?? "";

$method = $_SERVER["REQUEST_METHOD"];
if ($_SESSION["userRole"] === User::STAFF) {
    switch ($method) {
        case "POST":
            if ($subResource === "") {
                $date = $_POST["date"] ?? null;
                $condition = $_POST["condition"] ?? null;
                $doctor = $_POST["doctor"] ?? null;

                if (!isset($date) || !isset($condition) || !isset($doctor)) {
                    throw new Exception("Parameters missing", 400);
                }

                if (empty($date) || empty($condition) || empty($doctor)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $date = new DateTime($date);

                $newAppointment = new Appointment(0, $date, $condition, $doctor);
                $controller = new AppointmentController();

                try {
                    $controller->newRecord($newAppointment);
                    AuditGenerator::genarateLog("root", "Create appointment", Outcome::SUCCESS);
                    echo json_encode("Appointment created successfully");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Create appointment", Outcome::ERROR);
                    throw new Exception("Error creating new appointment: " . $e->getMessage(), 500);
                }
            } elseif ($subResource === "update") {
                $id = $_POST["appointmentID"] ?? null;
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $controller = new AppointmentController();
                try {
                    $appointment = $controller->getById($id);
                    if (isset($_POST["date"]) && !empty($_POST["date"])) {
                        $appointment->setDatetime(new DateTime($_POST["date"]));
                    }

                    if (isset($_POST["status"]) && !empty($_POST["status"])) {
                        $appointment->setStatus($_POST["status"]);
                    }

                    $controller->updateRecord($appointment);
                    AuditGenerator::genarateLog("root", "Update appointment", Outcome::SUCCESS);
                    echo json_encode("Appointment updated successfully");
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Update appointment", Outcome::ERROR);
                    throw new Exception("Error updating appointment: " . $e->getMessage(), 500);
                }
            } elseif ($subResource === "delete") {
                $id = $_POST["appointmentID"] ?? null;
                if (!isset($id)) {
                    throw new Exception("Parameters missing", 400);
                }
                if (empty($id)) {
                    throw new Exception("Parameters cannot be empty", 400);
                }

                $controller = new AppointmentController();
                try {
                    $controller->getById((int)$id);
                    $controller->deleteRecord((int)$id);
                    AuditGenerator::genarateLog("root", "Delete appointment", Outcome::SUCCESS);
                } catch (Exception $e) {
                    AuditGenerator::genarateLog("root", "Delete appointment", Outcome::ERROR);
                    throw new Exception("Error deleting appointment: " . $e->getMessage(), 500);
                }
            }
            break;
        default:
            throw new Exception("$method request method is not allowed", 405);
    }
} else {
    throw new Exception("User not allowed", 403);
}
