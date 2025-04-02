<?php

use models\AppItem;
use models\User;

interface ApplicationDAO
{
    public function getById(int $id): AppItem;
    public function getAll(): array;
    public function newRecord(AppItem $newRecord): bool;
}

class UserDAO implements ApplicationDAO
{
    public function getById(int $id): AppItem
    {
        return new User(1, "Steve Admin", "66678887", 'admin@example.com', '1234', User::STAFF);
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(AppItem $newRecord): bool
    {
        if ($newRecord instanceof User) {
            return true;
        } else {
            throw new InvalidArgumentException("Needs to be an user");
        }
    }
}

class AppointmentDAO implements ApplicationDAO
{
    public function getById(int $id): AppItem
    {
        return new User(1, "Steve Admin", "66678887", 'admin@example.com', '1234', User::STAFF);
    }

    public function getAll(): array
    {
        return array();
    }

    public function newRecord(AppItem $newRecord): bool
    {
        if ($newRecord instanceof User) {
            return true;
        } else {
            throw new InvalidArgumentException("Needs to be an user");
        }
    }
}
