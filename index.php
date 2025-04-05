<?php
header('Content-Type: application/json; charset=utf-8');

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$resource = strtolower($requestSegments[0]);

try {

    switch ($resource) {
        case "patients":
            require_once "./routes/patients.php";
            break;
        case "doctors":
            require_once "./routes/doctors.php";
            break;
        case "staff":
            require_once "./routes/staff.php";
            break;
        case "appointments":
            require_once "./routes/appointments.php";
            break;
        case "diagnoses":
            require_once "./routes/diagnoses.php";
            break;
        case "prescriptions":
            require_once "./routes/prescriptions.php";
            break;
        default:
            throw new Exception("Invalid resource $resource", 404);
    }
} catch (Exception $e) {
    echo $e->getMessage();
    http_response_code($e->getCode());
}
