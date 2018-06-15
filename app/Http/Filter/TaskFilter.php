<?php

namespace App\Http\Filter;

use Carbon\Carbon;

class TaskFilter extends QueryFilter
{
    public function title(string $searchString)
    {
        $this->getBuilder()->where('title', 'LIKE', "%{$searchString}%");
    }

    public function description(string $searchString)
    {
        $this->getBuilder()->where('description', 'LIKE', "%{$searchString}%");
    }

    public function startsOnFrom(string $dateTime)
    {
        $this->getBuilder()->where('starts_on', '>=', new Carbon($dateTime));
    }

    public function startsOnTo(string $dateTime)
    {
        $this->getBuilder()->where('starts_on', '<=', new Carbon($dateTime));
    }

    public function intervalTypeIn(string $intervalType)
    {
        $this->getBuilder()->where('interval_type', '=', $intervalType);
    }

    public function intervalFrom(int $interval)
    {
        $this->getBuilder()->where('interval', '>=', $interval);
    }

    public function intervalTo(float $interval)
    {
        $this->getBuilder()->where('interval', '<=', $interval);
    }
}
