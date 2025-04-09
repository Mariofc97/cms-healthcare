<?php

require_once __DIR__ . "/../controllers/auth.php";
require_once __DIR__ . '/../models/Audit.php';

use audit\AuditGenerator;
use audit\Outcome;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["userInfo"])) {
        $email = $_POST["email"] ?? null;
        $password = $_POST["password"] ?? null;

        if (!isset($email, $password)) {
            throw new Exception("Parameters missing", 400);
        }
        if (empty($email) || empty($password)) {
            throw new Exception("Parameters cannot be empty", 400);
        }

        $email = strip_tags($email);
        $email = htmlspecialchars($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalid", 400);
        }

        $password = strip_tags($password);
        try {
            $controller = new AuthController();
            if ($controller->authUser($email, $password)) {
                AuditGenerator::genarateLog($email, "Log in", Outcome::SUCCESS);
                echo json_encode("Successfully logged in with id: " . session_id());
            } else {
                AuditGenerator::genarateLog($email, "Log in", Outcome::ERROR);
                echo json_encode("Login failed");
                http_response_code(401);
            }
        } catch (Exception $e) {
            throw new Exception("There was an error loggin in: " . $e->getMessage(), $e->getCode());
        }
    } else {
        echo json_encode("Already logged in");
    }
} else {
    throw new Exception($_SERVER["REQUEST_METHOD"] . " request is not allowed", 405);
}
