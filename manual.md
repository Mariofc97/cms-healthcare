# CMS Healthcare - Documentation

## Overview

CMS Healthcare is a content management system designed for healthcare providers to manage patients, doctors, staff, appointments, diagnoses, prescriptions, and other related data. It provides a RESTful API for seamless integration and efficient data management.

---

## Features

-   User authentication and session management.
-   CRUD operations for patients, appointments, conditions, diagnoses, and prescriptions.
-   Audit logging for user actions.
-   Secure password hashing using `PASSWORD_ARGON2I`.

---

## Project Structure

### **Directories**

-   **`config/`**: Contains configuration files, such as `webConfig.php`, which defines database connection details and session timeout settings.
-   **`controllers/`**: Contains controllers for handling business logic.
    -   `auth.php`: Handles user authentication.
    -   `contentController.php`: Contains controllers for managing patients, staff, appointments, conditions, diagnoses, and prescriptions.
-   **`models/`**: Contains models representing the application's data structure.
    -   `User.php`: Defines user-related classes (e.g., `User`, `Patient`, `Doctor`, `Staff`).
    -   `Content.php`: Defines models for appointments, conditions, diagnoses, prescriptions, and symptoms.
    -   `Audit.php`: Handles audit logging for user actions.
-   **`routes/`**: Contains route handlers for API endpoints.
    -   `login.php`: Handles user login.
    -   `logout.php`: Handles user logout.
    -   `patients.php`: Handles patient-related operations.
    -   `appointments.php`: Handles appointment-related operations.
    -   `doctors.php`: Handles doctor-related operations.
    -   `staff.php`: Handles staff-related operations.
    -   `diagnoses.php`: Handles diagnosis-related operations.
    -   `prescriptions.php`: Handles prescription-related operations.
-   **`data/`**: Stores audit logs and uploaded files.
-   **`index.php`**: The main entry point for the application. Routes requests to the appropriate handlers.

---

## API Documentation

### **Authentication**

#### **1. `/login`**

-   **Method**: `POST`
-   **Description**: Authenticates a user and starts a session.
-   **Parameters**:
    -   `email` (string, required): The user's email address.
    -   `password` (string, required): The user's password.
-   **Response**:
    -   On success: Returns a session ID.
    -   On failure: Returns an error message.

#### **2. `/logout`**

-   **Method**: `GET`
-   **Description**: Logs out the current user by destroying the session.
-   **Parameters**: None.
-   **Response**:
    -   On success: Session is destroyed.

---

### **Patients**

#### **1. `/patients/medical-record`**

-   **Method**: `GET`
-   **Description**: Retrieves a patient's medical record.
-   **Parameters**:
    -   `patientID` (int, required): The ID of the patient.
-   **Response**:
    -   On success: Returns the patient's medical record.
    -   On failure: Returns an error message.

#### **2. `/patients` (Create Patient)**

-   **Method**: `POST`
-   **Description**: Creates a new patient record.
-   **Parameters**:
    -   `fname` (string, required): First name.
    -   `lname` (string, required): Last name.
    -   `phone` (string, optional): Phone number.
    -   `email` (string, required): Email address.
    -   `password` (string, required): Password.
    -   `gender` (string, required): Gender (`M` or `F`).
    -   `birthdate` (string, required): Birthdate in `YYYY-MM-DD` format.
    -   `address` (string, optional): Address.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

#### **3. `/patients/update`**

-   **Method**: `POST`
-   **Description**: Updates an existing patient record.
-   **Parameters**:
    -   `patientID` (int, required): The ID of the patient.
    -   Other fields (optional): Any of the fields from the "Create Patient" route can be updated.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

#### **4. `/patients/delete`**

-   **Method**: `POST`
-   **Description**: Deletes a patient record.
-   **Parameters**:
    -   `patientID` (int, required): The ID of the patient.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

---

### **Appointments**

#### **1. `/appointments` (Create Appointment)**

-   **Method**: `POST`
-   **Description**: Creates a new appointment.
-   **Parameters**:
    -   `date` (string, required): Appointment date in `YYYY-MM-DD HH:MM:SS` format.
    -   `condition` (int, required): Condition ID.
    -   `doctor` (int, required): Doctor ID.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

#### **2. `/appointments/update`**

-   **Method**: `POST`
-   **Description**: Updates an existing appointment.
-   **Parameters**:
    -   `appointmentID` (int, required): The ID of the appointment.
    -   `date` (string, optional): New appointment date.
    -   `status` (bool, optional): New status of the appointment.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

#### **3. `/appointments/delete`**

-   **Method**: `POST`
-   **Description**: Deletes an appointment.
-   **Parameters**:
    -   `appointmentID` (int, required): The ID of the appointment.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

---

### **Doctors**

#### **1. `/doctors`**

-   **Method**: `POST`
-   **Description**: Creates or updates doctor records.
-   **Parameters**:
    -   `fname`, `lname`, `email`, `password`, `specialty` (required for creation).
    -   `doctorID` (required for updates).
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

---

### **Staff**

#### **1. `/staff`**

-   **Method**: `POST`
-   **Description**: Manages staff records (create, update, delete).
-   **Parameters**:
    -   `fname`, `lname`, `email`, `password` (required for creation).
    -   `id` (required for updates or deletion).
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

---

### **Diagnoses**

#### **1. `/diagnoses`**

-   **Method**: `POST`
-   **Description**: Creates a new diagnosis.
-   **Parameters**:
    -   `appointmentID` (int, required): The ID of the appointment.
    -   `description` (string, required): Diagnosis description.
-   **Response**:
    -   On success: Returns a success message.
    -   On failure: Returns an error message.

---

### **Prescriptions**

#### **1. `/prescriptions`**

-   **Method**: `POST`
-   **Description**: Creates a new prescription.
-   **Parameters**:
    -   `medicine` (string, required): Medicine name.
    -   `dosage` (string, required): Dosage information.
    -   `doctorID` (int, required): Doctor ID.
    -   `diagnosisID` (int, required): Diagnosis ID.
    -   `dataFile` (file, optional): A file related to the prescription (e.g., PDF or image).
-   **Response**:
    -   On success: Returns a success message and uploads the file if provided.
    -   On failure: Returns an error message.

---

## Security Notes

-   Passwords are hashed using `password_hash` with the `PASSWORD_ARGON2I` algorithm.
-   Input validation and sanitization are performed to prevent SQL injection and XSS attacks.
-   Sessions are invalidated after a timeout defined in `SESSION_TIMEOUT`.
