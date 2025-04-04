<?php

require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;
use models\Appointment;

$method = $_SERVER["REQUEST_METHOD"];
switch ($method) {
    case "POST":
        $date = $_POST["date"];
        $condition = $_POST["condition"];
        $doctor = $_POST["doctor"];

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
        } catch (Exception $e) {
            AuditGenerator::genarateLog("root", "Create appointment", Outcome::ERROR);
            throw new Exception("Error creating new appointment: " . $e->getMessage(), 500);
        }
        break;
    case "PUT":
        break;
    default:
        throw new Exception("$method request method is not allowed", 405);
}
