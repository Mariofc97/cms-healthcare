<?php

declare(strict_types=1);

namespace models;

use DateTime;

interface AppItem {};

class User implements AppItem
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
        // TODO: CHANGE ID IN DATABASE
    }

    public function getName(): string
    {
        return $this->fname. " ". $this->lname;
    }

    public function getFname(): string {
        return $this->fname;
    }

    public function setFname(string $fname): void {
        $this->fname = $fname;
    }

    public function getLname(): string {
        return $this->lname;
    }

    public function setLname(string $lname): void {
        $this->lname = $lname;
    }

    public function setName(string $fname,string $lname,): void
    {
        $this->fname = $fname;
        $this->lname = $lname;
        // TODO: CHANGE NAME IN DATABASE
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
        // TODO: CHANGE PHONE IN DATABASE
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        // TODO: CHANGE EMAIL IN DATABASE
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
        // TODO: HASH PASSWORD
        // TODO: CHANGE PASSWORD IN DATABASE
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

final class Patient extends User
{
    private Gender $gender;
    private DateTime $birthdate;
    private string $address;

    public function __construct(
        int $id,
        string $name,
        string $phone,
        string $email,
        string $password,
        Gender $gender,
        DateTime $birthdate,
        string $address
    ) {
        parent::__construct($id, $name, $phone, $email, $password, User::PATIENT);
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
        // TODO: CHANGE GENDER IN DATABASE
    }

    public function getBirth(): DateTime
    {
        return $this->birthdate;
    }

    public function setBirth(DateTime $birthdate): void
    {
        $this->birthdate = $birthdate;
        // TODO: CHANGE BIRTHDATE IN DATABASE
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
        // TODO: CHANGE ADDRESS IN DATABASE
    }
}

final class Doctor extends User
{
    private string $specialty;

    public function __construct(
        int $id,
        string $name,
        string $phone,
        string $email,
        string $password,
        string $specialty
    ) {
        parent::__construct($id, $name, $phone, $email, $password, User::DOCTOR);
        $this->specialty = $specialty;
    }

    public function getSpecialty(): string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): void
    {
        $this->specialty = $specialty;
        // TODO: CHANGE SPECIALTY IN DATABASE
    }
}

final class Staff extends User{
    
}
