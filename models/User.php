<?php

declare(strict_types=1);

namespace models;

use DateTime;
use JsonSerializable;

class User
{
    protected int $id;
    protected string $fname;
    protected string $lname;
    protected string $phone;
    protected string $email;
    protected string $password;
    protected int $role;

    public const PATIENT = 1;
    public const DOCTOR = 2;
    public const STAFF = 3;

    public function __construct(int $id, string $fname, string $lname, string $phone, string $email, string $password, int $role)
    {
        $this->id = $id;
        $this->fname = $fname;
        $this->lname = $lname;
        $this->phone = $phone;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->fname . " " . $this->lname;
    }

    public function getFname(): string
    {
        return $this->fname;
    }

    public function setFname(string $fname): void
    {
        $this->fname = $fname;
    }

    public function getLname(): string
    {
        return $this->lname;
    }

    public function setLname(string $lname): void
    {
        $this->lname = $lname;
    }

    public function setName(string $fname, string $lname,): void
    {
        $this->fname = $fname;
        $this->lname = $lname;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function checkCredentials($inputEmail, $inputPassword): bool
    {
        return $this->email === $inputEmail && $this->password === $inputPassword;
    }
}

enum Gender
{
    case MALE;
    case FEMALE;
}

final class Patient extends User implements JsonSerializable
{
    private Gender $gender;
    private DateTime $birthdate;
    private string $address;

    public function __construct(
        int $id,
        string $lname,
        string $fname,
        string $phone,
        string $email,
        string $password,
        Gender $gender,
        DateTime $birthdate,
        string $address
    ) {
        parent::__construct($id, $fname, $lname, $phone, $email, $password, User::PATIENT);
        $this->gender = $gender;
        $this->birthdate = $birthdate;
        $this->address = $address;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    public function getBirth(): DateTime
    {
        return $this->birthdate;
    }

    public function setBirth(DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "Fname" => $this->fname,
            "Lname" => $this->lname,
            "Gender" => ($this->gender === Gender::FEMALE) ? "F" : "M",
            "Birthdate" => $this->birthdate->format("Y-m-d"),
            "Phone" => $this->phone,
            "Email" => $this->email,
            "Address" => $this->address
        ];
    }
}

final class Doctor extends User implements JsonSerializable
{
    private string $specialty;

    public function __construct(
        int $id,
        string $fname,
        string $lname,
        string $phone,
        string $email,
        string $password,
        string $specialty
    ) {
        parent::__construct($id, $fname, $lname, $phone, $email, $password, User::DOCTOR);
        $this->specialty = $specialty;
    }

    public function getSpecialty(): string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): void
    {
        $this->specialty = $specialty;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId(),
            "Fname" => $this->getFname(),
            "Lname" => $this->getLname(),
            "Phone" => $this->getPhone(),
            "Email" => $this->getEmail(),
            "Specialty" => $this->getSpecialty()
        ];
    }
}

final class Staff extends User {}
