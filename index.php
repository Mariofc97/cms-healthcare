<?php
$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$resource = strtolower($requestSegments[0]);

try {

    switch ($resource) {
        case "patients":
            // TODO: HANDLE PATIENT RELATED REQUESTS (E.G: MEDICAL RECORD, CURRENT CONDITION, APPOINTMENTS, ADD, REMOVE, UPDATE)
            require_once "./routes/patients.php";
            break;
        case "doctors":
            // TODO: HANDLE DOCTOR RELATED REQUESTS (E.G: APPOINTMENTS, ADD, REMOVE, UPDATE)
            require_once "./routes/doctors.php";
            break;
        case "staff":
            // TODO: HANDLE STAFF RELATED REQUESTS (E.G: ADD, REMOVE, UPDATE)
            require_once "./routes/staff.php";
            break;
        case "appointments":
            // TODO: HANDLE APPOINTMENT RELATED REQUESTS (E.G: ADD, REMOVE, UPDATE)
            require_once "./routes/appointments.php";
            break;
        case "diagnoses":
            // TODO: HANDLE DIAGNOSES RELATED REQUESTS (E.G: ADD, REMOVE, UPDATE)
            require_once "./routes/diagnoses.php";
            break;
        case "prescriptions":
            // TODO: HANDLE PRESCRIPTION RELATED REQUESTS (E.G: ADD, REMOVE, UPDATE)
            require_once "./routes/prescriptions.php";
            break;
        default:
            throw new Exception("Invalid resource $resource", 404);
    }
} catch (Exception $e) {
    echo $e->getMessage();
    http_response_code($e->getCode());
}
