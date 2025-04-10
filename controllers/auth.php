<?php
require_once __DIR__ . "/../config/webConfig.php";
require_once __DIR__ . "/contentController.php";

class AuthController extends ApplicationController
{
    public function authUser(string $email, string $password): bool
    {
        $sql = "SELECT * FROM user_tb WHERE Email = ? AND Activated = 1";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception("Login failed", 404);
            }
            $result = $result->fetch_assoc();
            $email = $result["Email"];
            $hashedPass = $result["Pass"];
            if (password_verify($password, $hashedPass)) {
                session_start();
                $_SESSION["userInfo"] = $result;
                $_SESSION["LAST_ACTIVITY"] = time();
                $stmt->close();
                return true;
            } else {
                $sql = "UPDATE user_tb SET AuthAttempt = AuthAttempt - 1 WHERE Email = ?";
                $stmt = $this->dbConnection->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();

                $sql = "SELECT AuthAttempt FROM user_tb WHERE Email = ?";
                $stmt = $this->dbConnection->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                $attemptsLeft = $result["AuthAttempt"];
                if ($attemptsLeft === 0) {
                    $sql = "UPDATE user_tb SET Activated = 0 WHERE Email = ?";
                    $stmt = $this->dbConnection->prepare($sql);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                }
                $stmt->close();
                return false;
            }
        } else {
            throw new Exception("Login failed", 500);
        }
    }
}
