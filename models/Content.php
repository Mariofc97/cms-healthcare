<?php

declare(strict_types=1);

namespace models;

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/../controllers/contentController.php";

use DateTime;
use JsonSerializable;

class Appointment implements AppItem, JsonSerializable
{
    private int $id;
    private DateTime $datetime;
    private bool $status;
    private int $condition;
    private int $doctor;

    public function __construct(int $id, DateTime $datetime, int $condition, int $doctor, bool $status = false)
    {
        $this->id = $id;
        $this->datetime = $datetime;
        $this->status = $status;
        $this->condition = $condition;
        $this->doctor = $doctor;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): void
    {
        $this->datetime = $datetime;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getCondition(): int
    {
        return $this->condition;
    }

    public function setCondition(int $condition): void
    {
        $this->condition = $condition;
    }

    public function getDoctor(): int
    {
        return $this->doctor;
    }

    public function setDoctor(int $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "Appointment_ID" => $this->id,
            "Appointment_Date" => $this->datetime,
            "Status" => $this->status
        ];
    }
}

class Condition implements AppItem, JsonSerializable
{
    private int $id;
    private DateTime $startDate;
    private int $patient;
    private array $symptoms;
    private array $diagnoses;

    public function __construct(int $id, DateTime $startDate, int $patient, array $symptoms = [], array $diagnoses = [])
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->patient = $patient;
        $this->symptoms = $symptoms;
        $this->diagnoses = $diagnoses;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getPatient(): int
    {
        return $this->patient;
    }

    public function setPatient(int $patient): void
    {
        $this->patient = $patient;
    }

    public function getSymptoms(): array
    {
        return $this->symptoms;
    }

    public function setSymptoms(array $symptoms): void
    {
        $this->symptoms = $symptoms;
    }

    public function getDiagnoses(): array
    {
        return $this->diagnoses;
    }

    public function setDiagnoses(array $diagnoses): void
    {
        $this->diagnoses = $diagnoses;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "Condition_ID" => $this->id,
            "StartDate" => $this->startDate,
            "Symptoms" => $this->symptoms,
            "Diagnoses" => $this->diagnoses
        ];
    }
}

class Diagnosis implements AppItem, JsonSerializable
{
    private int $id;
    private string $description;
    private int $apppointment;
    private array $prescriptions;
    private array $prescriptions;

    public function __construct(int $id, string $description, int $apppointment, array $prescriptions = [])
    {
        $this->id = $id;
        $this->description = $description;
        $this->apppointment = $apppointment;
        $this->prescriptions = $prescriptions;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getApppointment(): int
    {
        return $this->apppointment;
    }

    public function setApppointment(int $apppointment): void
    {
        $this->apppointment = $apppointment;
    }

    public function getPrescriptions(): array
    {
        return $this->prescriptions;
    }

    public function setPrescriptions(array $prescriptions): void
    {
        $this->prescriptions = $prescriptions;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'prescriptions' => $this->prescriptions
        ];
    }
}


class Prescription implements AppItem
{
    private int $id;
    private string $medicine;
    private string $dosage;

    public function __construct(int $id, string $medicine, string $dosage)
    {
        $this->id = $id;
        $this->medicine = $medicine;
        $this->dosage = $dosage;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getMedicine(): string
    {
        return $this->medicine;
    }

    public function setMedicine(string $medicine): void
    {
        $this->medicine = $medicine;
    }

    public function getDosage(): string
    {
        return $this->dosage;
    }

    public function setDosage(string $dosage): void
    {
        $this->dosage = $dosage;
    }
}

class Symptom implements AppItem, JsonSerializable
{
    private int $condition;
    private string $symptom;

    public function __construct(int $condition, string $symptom)
    {
        $this->condition = $condition;
        $this->symptom = $symptom;
    }

    public function getCondition(): int
    {
        return $this->condition;
    }

    public function setCondition(int $condition): void
    {
        $this->condition = $condition;
    }

    public function getSymptom(): string
    {
        return $this->symptom;
    }

    public function setSymptom(string $symptom): void
    {
        $this->symptom = $symptom;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "symptom" => $this->symptom
        ];
    }
}

class MedicalRecord implements AppItem, JsonSerializable
{
    private Patient $patient;
    private array $conditions;

    public function __construct(Patient $patient, array $conditions)
    {
        $this->patient = $patient;
        $this->conditions = $conditions;
    }

    public function getPatient(): Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): void
    {
        $this->patient = $patient;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "patientInfo" => $this->patient,
            "patientConditions" => $this->conditions
        ];
    }
}

class DetailedAppointment implements AppItem, JsonSerializable
{
    private Doctor $doctor;
    private Appointment $appointment;

    public function __construct(Doctor $doctor, Appointment $appointment)
    {
        $this->doctor = $doctor;
        $this->appointment = $appointment;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function getAppointment(): Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(Appointment $appointment): void
    {
        $this->appointment = $appointment;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "doctorInfo" => [
                "Fname" => $this->doctor->getFname(),
                "Lname" => $this->doctor->getLname(),
                "Specialty" => $this->doctor->getSpecialty()
            ],
            "appointmentInfo" => $this->appointment
        ];
    }
}
