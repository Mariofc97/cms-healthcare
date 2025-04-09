<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/config/webConfig.php";

$requestUri = trim($_SERVER["PATH_INFO"], "/") ?? "";

$requestSegments = explode("/", $requestUri);
$resource = strtolower($requestSegments[0]);

try {

    if ($resource === "login") {
        require_once "./routes/login.php";
    } elseif ($resource === "logout") {
        require_once "./routes/logout.php";
    } else {
        if (!isset($_REQUEST["sid"])) {
            throw new Exception("Session ID missing", 400);
        }
        session_id($_REQUEST["sid"]);
        session_start();
        if (isset($_SESSION["LAST_ACTIVITY"]) && (time() - $_SESSION["LAST_ACTIVITY"]) > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            throw new Exception("Session invalid", 408);
        } else {
            $_SESSION["LAST_ACTIVITY"] = time();
        }

        if (isset($_SESSION["userInfo"])) {
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
        } else {
            throw new Exception("User needs to be logged in", 401);
        }
    }
} catch (Exception $e) {
    echo json_encode($e->getMessage());
    http_response_code($e->getCode());
}
