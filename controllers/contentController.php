<?php

use models\AppItem;
use models\Appointment;
use models\User;

include "../config/webConfig.php";

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

    public function getById(int $id): AppItem
    {
        return new User(1, "Steve Admin", "66678887", 'admin@example.com', '1234', User::STAFF);
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(Appointment $newRecord): bool
    {
        $date = $newRecord->getDatetime()->getTimestamp();
        $condition = $newRecord->getCondition();
        $doctor = $newRecord->getDoctor();
        $sql = "INSERT INTO appointment(Appointment_Date, Condition_ID, Doctor_ID) VALUES ($date, $condition, $doctor)";

        if ($this->dbConnection->query($sql)) {
            return true;
        }
        return false;
    }

    public function updateRecord(AppItem $updatedItem): bool
    {
        return false;
    }

    public function __destruct()
    {
        $this->dbConnection->close();
    }
}
