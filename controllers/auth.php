<?php
session_start();

require_once __DIR__ . "/../config/webConfig.php";
require_once __DIR__ . "/contentController.php";

class authController extends ApplicationController
{
    public function authUser(string $email, string $password): bool
    {
        $sql = "SELECT * FROM user_tb WHERE Email = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Login failed", 404);
            }
            $result = $result->fetch_assoc();
            $userID = $result["User_ID"];
            $hashedPass = $result["Pass"];
            if (password_verify($password, $hashedPass)) {
                $_SESSION["userID"] = $userID;
                $_SESSION["userRole"] = $result["Type"];
                return true;
            } else {
                return false;
            }
        } else {
            throw new Exception("Login failed", 400);
        }
    }
}
