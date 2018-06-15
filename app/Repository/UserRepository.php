<?php

namespace App\Repository;

use App\User;

class UserRepository extends Repository
{
    protected function getModelClass(): string
    {
        return User::class;
    }
}
