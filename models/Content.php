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

class Condition implements AppItem
{
    private int $id;
    private DateTime $startDate;
    private int $patient;

    public function __construct(int $id, DateTime $startDate, int $patient)
    {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->patient = $patient;
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
}

class Diagnosis implements AppItem
{
    private int $id;
    private string $description;
    private int $apppointment;

    public function __construct(int $id, string $description, int $apppointment)
    {
        $this->id = $id;
        $this->description = $description;
        $this->apppointment = $apppointment;
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

class Symptom implements AppItem
{
    private int $id;
    private string $description;

    public function __construct(int $id, string $description)
    {
        $this->id = $id;
        $this->description = $description;
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
}
