<?php

namespace App\Repository;

use App\Verification;

class VerificationRepository extends Repository
{
    protected function getModelClass(): string
    {
        return Verification::class;
    }
}
