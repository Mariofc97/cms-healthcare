<?php
class User {
    public $email;
    public $password;
    public $role;

    public function __construct($email, $password, $role) {
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function checkCredentials($inputEmail, $inputPassword) {
        return $this->email === $inputEmail && $this->password === $inputPassword;
    }
}
?>
