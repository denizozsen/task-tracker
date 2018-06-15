<?php

namespace App;

use App\Http\Filter\QueryFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Task model.
 *
 * @property int $id
 * @property int $user
 * @property string $title task title, with character limit of 250
 * @property string $description
 * @property Carbon|string $starts_on
 * @property string $interval_type one of: 'daily', 'weekly', 'monthly', 'yearly'
 * @property int $interval default: 1
 * @property Carbon|string|null $created_at
 * @property Carbon|string|null $updated_at
 */
class Task extends Model
{
    use HasTimestamps;

    const DAY   = 'day';
    const WEEK  = 'week';
    const MONTH = 'month';
    const YEAR  = 'year';

    public function scopeFilter(Builder $builder, QueryFilter $filter)
    {
        return $filter->apply($builder);
    }
}
