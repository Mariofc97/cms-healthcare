<?php

declare(strict_types=1);

namespace authentication;

function getAllUsers()
{
    return [
        new User(1, "Steve Admin", "66678887", 'admin@example.com', '1234', User::STAFF),
        new User(2, "Dr. Joseph", "4566754", 'doctor@example.com', '0987', User::DOCTOR),
    ];
}
