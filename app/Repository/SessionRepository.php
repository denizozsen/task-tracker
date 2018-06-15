<?php

namespace App\Repository;

use App\Session;

class SessionRepository extends Repository
{
    protected function getModelClass(): string
    {
        return Session::class;
    }
}
