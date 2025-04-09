# CMS Healthcare

## Overview

CMS Healthcare is a content management system designed for healthcare providers to manage patients, doctors, staff, appointments, diagnoses, prescriptions, and other related data. It provides a RESTful API for seamless integration and efficient data management.

---

## Features

-   User authentication and session management.
-   CRUD operations for patients, appointments, conditions, and more.
-   Audit logging for user actions.
-   Secure password hashing using `PASSWORD_ARGON2I`.

---

## Setup Instructions

### Prerequisites

1. **AMPPS or XAMPP**: Ensure you have a local server environment installed.
2. **PHP**: Version 7.4 or higher.
3. **MySQL**: A running MySQL server.

### Steps to Set Up

1. **Clone the Repository**:
    ```bash
    git clone https://github.com/your-repo/cms-healthcare.git
    cd cms-healthcare
    ```
2. **Create a database called healthcare_cms in MySQL**
3. **With the created database, create the tables using the script**:

    ```sql
    CREATE TABLE USER_TB(
        User_ID int NOT NULL AUTO_INCREMENT,
        Fname varchar(50) NOT NULL,
        Lname varchar(70) NOT NULL,
        Phone varchar(11) DEFAULT 'No Phone',
        Email varchar(50) NOT NULL,
        Pass varchar(300) NOT NULL,
        Activated boolean DEFAULT 1,
        AuthAttempt tinyint DEFAULT 5,
        Type int,
        PRIMARY KEY(User_ID),
        UNIQUE(Email)
    );

    CREATE TABLE PATIENT (
        Patient_ID int NOT NULL,
        Gender char(1) NOT NULL,
        Birthdate date NOT NULL,
        Address varchar(100) DEFAULT 'No address',
        PRIMARY KEY(Patient_ID),
        FOREIGN KEY(Patient_ID) REFERENCES USER_TB(User_ID)
    );

    CREATE TABLE DOCTOR (
        Doctor_ID int NOT NULL,
        Specialty varchar(70) NOT NULL,
        PRIMARY KEY(Doctor_ID),
        FOREIGN KEY(Doctor_ID) REFERENCES USER_TB(User_ID)
    );

    CREATE TABLE PT_CONDITION (
        Condition_ID int NOT NULL AUTO_INCREMENT,
        StartDate datetime NOT NULL,
        Patient_ID int NOT NULL,
        PRIMARY KEY(Condition_ID),
        FOREIGN KEY(Patient_ID) REFERENCES PATIENT(Patient_ID)
    );

    CREATE TABLE APPOINTMENT (
        Appointment_ID int NOT NULL AUTO_INCREMENT,
        Appointment_Date datetime NOT NULL,
        Staus boolean DEFAULT 0,
        Condition_ID int NOT NULL,
        Doctor_ID int NOT NULL,
        PRIMARY KEY(Appointment_ID),
        UNIQUE(Appointment_Date, Doctor_ID),
        FOREIGN KEY(Condition_ID) REFERENCES PT_CONDITION(Condition_ID),
        FOREIGN KEY(Doctor_ID) REFERENCES DOCTOR(Doctor_ID)
        );

    CREATE TABLE DIAGNOSIS (
        Diagnosis_ID int NOT NULL AUTO_INCREMENT,
        Description text NOT NULL,
        Appointment_ID int NOT NULL,
        PRIMARY KEY(Diagnosis_ID),
        FOREIGN KEY(Appointment_ID) REFERENCES APPOINTMENT(Appointment_ID)
    );

    CREATE TABLE PRESCRIPTION (
        Prescription_ID int NOT NULL AUTO_INCREMENT,
        Medicine varchar(100) NOT NULL,
        Dosage varchar(30) NOT NULL,
        PRIMARY KEY(Prescription_ID)
    );

    CREATE TABLE PRESCRIBE_REL (
        Doctor_ID int NOT NULL,
        Diagnosis_ID int NOT NULL,
        Prescription_ID int NOT NULL,
        PRIMARY KEY(Doctor_ID, Diagnosis_ID, Prescription_ID),
        FOREIGN KEY(Doctor_ID) REFERENCES DOCTOR(Doctor_ID),
        FOREIGN KEY(Diagnosis_ID) REFERENCES DIAGNOSIS(Diagnosis_ID),
        FOREIGN KEY(Prescription_ID) REFERENCES PRESCRIPTION(Prescription_ID)
    );

    CREATE TABLE CONDITION_SYMPTOM (
        Condition_ID int NOT NULL,
        Symptom varchar(100) NOT NULL,
        PRIMARY KEY(Condition_ID, Symptom),
        FOREIGN KEY(Condition_ID) REFERENCES PT_CONDITION(Condition_ID)
    );
    ```

4. **Change the [`webConfig.php`](config/webConfig.php) to include your user access to the MySQL server**:

    ```php
    <?php

    define("DB_SERVER", "localhost");
    define("DB_USERNAME", "your_username");
    define("DB_PASS", "your_password");
    define("DB_NAME", "healthcare_cms");
    define("SESSION_TIMEOUT", 1800);
    ```

5. **Start running the server and make the requests!**
