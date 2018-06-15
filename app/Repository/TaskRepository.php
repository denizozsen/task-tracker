<?php

namespace App\Repository;

use App\Task;

class TaskRepository extends Repository
{
    protected function getModelClass(): string
    {
        return Task::class;
    }
}
