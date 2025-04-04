<?php

use models\AppItem;
use models\Appointment;
use models\User;

require_once __DIR__ . "/../config/webConfig.php";

class UserController
{
    public function getById(int $id): AppItem
    {
        return new User(1, "Steve Admin", "66678887", 'admin@example.com', '1234', User::STAFF);
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(AppItem $newRecord): bool
    {
        if ($newRecord instanceof User) {
            return true;
        } else {
            throw new InvalidArgumentException("Needs to be an user");
        }
    }

    public function updateRecord(AppItem $updatedItem): bool
    {
        return true;
    }
}

class AppointmentController
{
    private mysqli $dbConnection;

    public function __construct()
    {
        $this->dbConnection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASS, DB_NAME);
    }

    public function getById(int $id): Appointment
    {
        $sql = "SELECT * FROM appointment WHERE Appointment_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();

        if ($result) {
            return new Appointment($result["Appointment_ID"], new DateTime($result["Appointment_Date"]), $result["Condition_ID"], $result["Doctor_ID"], $result["Status"]);
        } else throw new Exception("Appointment not found with ID $id");
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(Appointment $newRecord): bool
    {
        $date = $newRecord->getDatetime()->format('Y-m-d H:i:s');
        $condition = $newRecord->getCondition();
        $doctor = $newRecord->getDoctor();
        $sql = "INSERT INTO appointment(Appointment_Date, Condition_ID, Doctor_ID) VALUES (?, ?, ?)";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("sii", $date, $condition, $doctor);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function updateRecord(Appointment $updatedItem): bool
    {
        $date = $updatedItem->getDatetime()->format('Y-m-d H:i:s');
        $status = $updatedItem->getStatus();
        $id = $updatedItem->getId();

        $sql = "UPDATE appointment SET Appointment_DATE = ?, Status = ? WHERE Appointment_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("sii", $date, $status, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    function __destruct()
    {
        $this->dbConnection->close();
    }
}
