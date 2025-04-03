<?php

use models\AppItem;
use models\User;

interface ApplicationController
{
    public function getById(int $id): AppItem;
    public function getAll(): array;
    public function newRecord(AppItem $newRecord): bool;
}

class UserController implements ApplicationController
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

class AppointmentController implements ApplicationController
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
