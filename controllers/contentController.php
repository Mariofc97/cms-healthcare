<?php

use models\AppItem;
use models\Appointment;
use models\Diagnosis;
use models\User;
use models\Staff;

include __DIR__ . "/../config/webConfig.php";

abstract class ApplicationController {
    protected mysqli $dbConnection;

    public function __construct()
    {
        $this->dbConnection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASS, DB_NAME);
    }

    public function __destruct()
    {
        $this->dbConnection->close();
    }
}

class StaffController extends ApplicationController
{
    public function getById(int $id): Staff
    {
        $sql = "SELECT * FROM USER_TB WHERE User_ID = ? AND Type = ? AND Activated = 1";
        $stmt = $this->dbConnection->prepare($sql);
        $type = User::STAFF;
        $stmt->bind_param('ii',$id, $type);

        if(!$stmt->execute()){
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();

        if($result){
            return new Staff($result["User_ID"], $result["Fname"], $result["Lname"], $result["Phone"], $result["Email"], $result["Pass"], User::STAFF);
        }else{
            throw new Exception("Staff not found with ID $id");
        }
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(User $newRecord): bool
    {
        $fname = $newRecord->getFname();
        $lname = $newRecord->getLname();
        $phone = $newRecord->getPhone();
        $email = $newRecord->getEmail();
        $password = $newRecord->getPassword();

        $sql = "INSERT INTO USER_TB(Fname, Lname, Phone, Email, Pass, Type) VALUES(?,?,?,?,?,?)";
        $stmt = $this->dbConnection->prepare($sql);
        $type = User::STAFF;
        $stmt->bind_param('sssssi',$fname, $lname, $phone, $email, $password, $type);

        if($stmt->execute()){
            return true;
        }else{
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
    }

    public function updateRecord(Staff $updatedItem): bool
    {
        $id = $updatedItem->getId();  //User.php > Staff
        $fname = $updatedItem->getFname();
        $lname = $updatedItem->getLname();
        $phone = $updatedItem->getPhone();
        $email = $updatedItem->getEmail();
        $password = $updatedItem->getPassword();

        $sql = "UPDATE USER_TB SET Fname=?, Lname=?, Phone=?, Email=?, Pass=? WHERE User_ID=?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('sssssi', $fname, $lname, $phone, $email, $password, $id);
        if(!$stmt->execute()){
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        return true;
    }

    public function deleteStaff(int $id): bool {
        $sql = "UPDATE USER_TB SET Activated=0 WHERE User_ID=?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('i', $id);
        if(!$stmt->execute()){
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
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

class DiagnosisController extends ApplicationController{
    public function getById(int $id): Diagnosis{
        $sql = "SELECT * FROM DIAGNOSIS WHERE Diagnosis_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('i', $id);

        if(!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();

        if($result){
            return new Diagnosis($result["Diagnosis_ID"], $result["Description"], $result["Appointment_ID"]);
        }else{
            throw new Exception("Diagnosis not found with ID $id");
        }
    }

    public function newRecord(Diagnosis $newRecord): bool //Content.php
    {
        $description = $newRecord->getDescription();
        $appointmentid = $newRecord->getApppointment();
        // var_dump($description, $appointmentid);
        $sql = "INSERT INTO DIAGNOSIS(Description, Appointment_ID) VALUES(?, ?)";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('si', $description, $appointmentid);

        if($stmt->execute()) {
            return true;
        }else{
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
    }
}

class PrescriptionController extends ApplicationController {
    public function getByDiagnosis(int $diagnosisID): array {
        $sql = "SELECT PRESCRIPTION.Prescription_ID, Medicine, Dosage
        FROM PRESCRIPTION INNER JOIN PRESCRIBE_REL
        ON PRESCRIPTION.Prescription_ID = PRESCRIBE_REL.Prescription_ID
        INNER JOIN DIAGNOSIS
        ON DIAGNOSIS.Diagnosis_ID = PRESCRIBE_REL.Diagnosis_ID
        WHERE DIAGNOSIS.Diagnosis_ID = ?";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $diagnosisID);

        if(!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }

        $prescriptions = [];

        $result = $stmt->get_result();

        if(!$result){
            throw new Exception("Diagnosis not found with ID $diagnosisID");
        }

        while($prescription = $result->fetch_assoc()){
            $prescriptions[] = $prescription;
        }

        return $prescriptions;
    }
}