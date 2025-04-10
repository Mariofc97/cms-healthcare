<?php

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

if ($_SESSION["userInfo"]["Type"] === User::STAFF) {
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

                $date = htmlspecialchars(strip_tags($date));
                $condition = htmlspecialchars(strip_tags($condition));
                $doctor = htmlspecialchars(strip_tags($doctor));

                if (!filter_var($condition, FILTER_VALIDATE_INT)) {
                    throw new Exception("Invalid condition ID number", 400);
                }

                if (!filter_var($doctor, FILTER_VALIDATE_INT)) {
                    throw new Exception("Invalid doctor ID number", 400);
                }

                try {
                    $date = new DateTime($date);
                    $newAppointment = new Appointment(0, $date, $condition, $doctor);
                    $controller = new AppointmentController();
                    $controller->newRecord($newAppointment);
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Create appointment", Outcome::SUCCESS);
                    echo json_encode("Appointment created successfully");
                } catch (Exception $e) {
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Create appointment", Outcome::ERROR);
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

                $id = htmlspecialchars(strip_tags($id));

                if (!filter_var($id, FILTER_VALIDATE_INT)) {
                    throw new Exception("Invalid appointment ID", 400);
                }

                try {
                    $controller = new AppointmentController();
                    $appointment = $controller->getById($id);
                    $date = $_POST["date"] ?? null;
                    if (isset($date) && !empty($date)) {
                        $date = htmlspecialchars(strip_tags($date));
                        $appointment->setDatetime(new DateTime($_POST["date"]));
                    }

                    $status = $_POST["status"] ?? null;
                    if (isset($status) && !empty($status)) {
                        $status = htmlspecialchars(strip_tags($status));
                        if (!filter_var($status, FILTER_VALIDATE_BOOLEAN)) {
                            throw new Exception("Invalid status", 400);
                        }
                        $appointment->setStatus($_POST["status"]);
                    }

                    $controller->updateRecord($appointment);
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Update appointment", Outcome::SUCCESS);
                    echo json_encode("Appointment updated successfully");
                } catch (Exception $e) {
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Update appointment", Outcome::ERROR);
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

                $id = htmlspecialchars(strip_tags($id));

                if (!filter_var($id, FILTER_VALIDATE_INT)) {
                    throw new Exception("Invalid appointment ID", 400);
                }

                try {
                    $controller = new AppointmentController();
                    $controller->getById((int)$id);
                    $controller->deleteRecord((int)$id);
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Delete appointment", Outcome::SUCCESS);
                    echo json_encode("Appointment deleted successfully");
                } catch (Exception $e) {
                    AuditGenerator::generateLog($_SESSION["userInfo"]["Email"], "Delete appointment", Outcome::ERROR);
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
