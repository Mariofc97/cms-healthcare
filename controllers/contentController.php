<?php

use models\AppItem;
use models\Appointment;
use models\Condition;
use models\Gender;
use models\MedicalRecord;
use models\Patient;
use models\Staff;
use models\Symptom;
use models\User;
use models\Diagnosis;
use models\Doctor;

require_once __DIR__ . "/../config/webConfig.php";

abstract class ApplicationController
{
    protected mysqli $dbConnection;

    public function __construct()
    {
        $this->dbConnection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASS, DB_NAME);
        if ($this->dbConnection->connect_error) {
            throw new Exception("Error connecting to the database: " . $this->dbConnection->connect_error);
        }
    }

    public function __destruct()
    {
        $this->dbConnection->close();
    }
}

class PatientController extends ApplicationController
{
    public function getById(int $id): Patient
    {
        $sql = "SELECT * FROM patient INNER JOIN user_tb WHERE User_ID = ? AND Type = ? AND Activated = 1";
        $stmt = $this->dbConnection->prepare($sql);
        $type = User::PATIENT;
        $stmt->bind_param("ii", $id, $type);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();

        if ($result) {
            $result["Gender"] = ($result["Gender"] === "F") ? Gender::FEMALE : Gender::MALE;
            return new Patient(
                $result["User_ID"],
                $result["Lname"],
                $result["Fname"],
                $result["Phone"],
                $result["Email"],
                $result["Pass"],
                $result["Gender"],
                new DateTime($result["Birthdate"]),
                $result["Address"]
            );
        } else throw new Exception("Patient not found with ID $id");
    }

    public function getAll(int $id): array
    {
        return array();
    }

    public function newRecord(Patient $newRecord): bool
    {
        $fname = $newRecord->getFname();
        $lname = $newRecord->getLname();
        $phone = $newRecord->getPhone();
        $email = $newRecord->getEmail();
        $password = $newRecord->getPassword();

        $sql = "INSERT INTO user_tb(Fname, Lname, Phone, Email, Pass, Type) VALUES(?,?,?,?,?,?)";
        $stmt = $this->dbConnection->prepare($sql);
        $type = User::PATIENT;
        $stmt->bind_param('sssssi', $fname, $lname, $phone, $email, $password, $type);

        if ($stmt->execute()) {
            $id = $this->dbConnection->insert_id;
            $gender = ($newRecord->getGender() === Gender::MALE) ? "M" : "F";
            $birthdate = $newRecord->getBirth()->format("Y-m-d H:i:s");
            $address = $newRecord->getAddress();

            $sql = "INSERT INTO patient VALUES(?,?,?, ?)";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("isss", $id, $gender, $birthdate, $address);

            if ($stmt->execute()) {
                return true;
            } else throw new Exception($this->dbConnection->error);
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function updateRecord(Patient $updatedRecord)
    {
        $id = $updatedRecord->getId();
        $fname = $updatedRecord->getFname();
        $lname = $updatedRecord->getLname();
        $phone = $updatedRecord->getPhone();
        $email = $updatedRecord->getEmail();
        $pass = $updatedRecord->getPassword();
        $gender = ($updatedRecord->getGender() === Gender::FEMALE) ? "F" : "M";
        $birthdate = $updatedRecord->getBirth()->format("Y-m-d");
        $address = $updatedRecord->getAddress();

        $sql = "UPDATE user_tb SET Fname = ?, Lname = ?, Phone = ?, Email = ?, Pass = ? WHERE User_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("sssssi", $fname, $lname, $phone, $email, $pass, $id);

        if ($stmt->execute()) {
            $sql = "UPDATE patient SET Gender = ?, Birthdate = ?, Address = ? WHERE Patient_ID = ?";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param("sssi", $gender, $birthdate, $address, $id);
            if ($stmt->execute()) {
                return true;
            } else throw new Exception($this->dbConnection->error);
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function deleteRecord(int $id): bool
    {
        $sql = "UPDATE user_tb SET Activated = 0 WHERE User_ID = ? AND Type = ?";
        $stmt = $this->dbConnection->prepare($sql);

        $type = User::PATIENT;
        $stmt->bind_param("ii", $id, $type);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }
}

class StaffController extends ApplicationController
{
    public function getById(int $id): Staff
    {
        return new Staff(1, "Steve", "Admin", "66678887", 'admin@example.com', '1234', User::STAFF);
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(Staff $newRecord): bool
    {
        $fname = $newRecord->getFname();
        $lname = $newRecord->getLname();
        $phone = $newRecord->getPhone();
        $email = $newRecord->getEmail();
        $password = $newRecord->getPassword();

        $sql = "INSERT INTO USER_TB(Fname, Lname, Phone, Email, Pass, Type) VALUES(?,?,?,?,?,?)";
        $stmt = $this->dbConnection->prepare($sql);
        $type = User::STAFF;
        $stmt->bind_param('sssssi', $fname, $lname, $phone, $email, $password, $type);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function updateRecord(AppItem $updatedItem): bool
    {
        return true;
    }
}

class AppointmentController extends ApplicationController
{
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

    public function deleteRecord(int $id): bool
    {
        $sql = "DELETE FROM appointment WHERE Appointment_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function getByPatient(int $patientId): array
    {
        $sql = "SELECT appointment.Appointment_ID, Appointment_Date, Status, appointment.Condition_ID, appointment.Doctor_ID
        FROM appointment INNER JOIN pt_condition
        ON appointment.Condition_ID = pt_condition.Condition_ID
        INNER JOIN patient 
        ON patient.Patient_ID = pt_condition.Patient_ID
        WHERE patient.Patient_ID = ? AND Status = 0";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $patientId);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }

        $appointments = [];

        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Appointments not found with ID $patientId");
        }

        while ($appointment = $result->fetch_assoc()) {
            $appointments[] = new Appointment(
                $appointment["Appointment_ID"],
                new DateTime($appointment["Appointment_Date"]),
                $appointment["Condition_ID"],
                $appointment["Doctor_ID"]
            );
        }

        return $appointments;
    }

    public function getDoctorInfo(int $appointmentId): Doctor
    {
        $sql = "SELECT doctor.Doctor_ID, doctor.Specialty, user_tb.Fname, user_tb.Lname, user_tb.Email, user_tb.Pass, user_tb.Phone
        FROM appointment INNER JOIN doctor
        ON appointment.Doctor_ID = doctor.Doctor_ID
        INNER JOIN user_tb
        ON doctor.Doctor_ID = user_tb.User_ID
        WHERE Appointment_ID = ?";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $appointmentId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            return new Doctor(
                $result["Doctor_ID"],
                $result["Fname"],
                $result["Lname"],
                $result["Phone"],
                $result["Email"],
                $result["Pass"],
                $result["Specialty"]
            );
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }
}

class ConditionController extends ApplicationController
{
    public function newRecord(Condition $newRecord): bool
    {
        $startDate = $newRecord->getStartDate()->format("Y-m-d H:i:s");
        $patient = $newRecord->getPatient();
        $symptoms = $newRecord->getSymptoms();

        $sql = "INSERT INTO pt_condition(StartDate, Patient_ID) VALUES (?, ?)";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("si", $startDate, $patient);

        if ($stmt->execute()) {
            $id = $this->dbConnection->insert_id;
            $sql = "INSERT INTO condition_symptom VALUES (?, ?)";
            foreach ($symptoms as $symptom) {
                $stmt = $this->dbConnection->prepare($sql);
                $description = $symptom->getSymptom();
                $stmt->bind_param("is", $id, $description);
                if (!$stmt->execute()) {
                    throw new Exception($this->dbConnection->error);
                }
            }
            return true;
        } else {
            throw new Exception($this->dbConnection->error);
        }
    }

    public function getByPatient(int $patientId): array
    {
        $sql = "SELECT Condition_ID, StartDate FROM 
        pt_condition INNER JOIN patient
        ON pt_condition.Patient_ID = patient.Patient_ID
        WHERE patient.Patient_ID = ?
        ORDER BY pt_condition.StartDate ASC";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $patientId);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }

        $conditions = [];
        $result = $stmt->get_result();

        while ($condition = $result->fetch_assoc()) {
            $conditions[] = new Condition($condition["Condition_ID"], new DateTime($condition["StartDate"]), $patientId);
        }

        foreach ($conditions as $condition) {
            $condition->setSymptoms($this->getSymptoms($condition->getId()));
        }

        return $conditions;
    }

    public function getSymptoms(int $conditionID): array
    {
        $sql = "SELECT Symptom, condition_symptom.Condition_ID FROM
        condition_symptom INNER JOIN pt_condition
        ON condition_symptom.Condition_ID = pt_condition.Condition_ID
        WHERE pt_condition.Condition_ID = ?";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $conditionID);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }

        $symptoms = [];
        $result = $stmt->get_result();

        while ($symptom = $result->fetch_assoc()) {
            $symptoms[] = new Symptom($symptom["Condition_ID"], $symptom["Symptom"]);
        }

        return $symptoms;
    }
}

class DiagnosisController extends ApplicationController
{
    public function getById(int $id): Diagnosis
    {
        $sql = "SELECT * FROM DIAGNOSIS WHERE Diagnosis_ID = ?";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();

        if ($result) {
            return new Diagnosis($result["Diagnosis_ID"], $result["Description"], $result["Appointment_ID"]);
        } else {
            throw new Exception("Diagnosis not found with ID $id");
        }
    }

    public function getByCondition(int $conditionId): array
    {
        $sql = "SELECT Diagnosis_ID, Description, diagnosis.Appointment_ID FROM
        diagnosis INNER JOIN appointment
        ON diagnosis.Appointment_ID = appointment.Appointment_ID
        INNER JOIN pt_condition
        ON appointment.Condition_ID = pt_condition.Condition_ID
        WHERE pt_condition.Condition_ID = ?";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $conditionId);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }
        $result = $stmt->get_result();

        $diagnoses = [];
        while ($diagnosis = $result->fetch_assoc()) {
            $diagnoses[] = new Diagnosis($diagnosis["Diagnosis_ID"], $diagnosis["Description"], $diagnosis["Appointment_ID"]);
        }

        return $diagnoses;
    }
}

class PrescriptionController extends ApplicationController
{
    public function getByDiagnosis(int $diagnosisID): array
    {
        $sql = "SELECT PRESCRIPTION.Prescription_ID, Medicine, Dosage
        FROM PRESCRIPTION INNER JOIN PRESCRIBE_REL
        ON PRESCRIPTION.Prescription_ID = PRESCRIBE_REL.Prescription_ID
        INNER JOIN DIAGNOSIS
        ON DIAGNOSIS.Diagnosis_ID = PRESCRIBE_REL.Diagnosis_ID
        WHERE DIAGNOSIS.Diagnosis_ID = ?";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bind_param("i", $diagnosisID);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $this->dbConnection->error);
        }

        $prescriptions = [];

        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Diagnosis not found with ID $diagnosisID");
        }

        while ($prescription = $result->fetch_assoc()) {
            $prescriptions[] = $prescription;
        }

        return $prescriptions;
    }
}
