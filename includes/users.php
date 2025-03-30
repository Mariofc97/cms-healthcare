<?php
require_once __DIR__.'/../models/User.php';

return [
    new User('admin@example.com','1234','admin'),
    new User('doctor@example.com', '0987','doctor'),
];
?>