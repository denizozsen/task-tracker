<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Base class for all model repositories. Provides methods for creating and fetching models.
 */
abstract class Repository
{
    protected abstract function getModelClass(): string;

    public function createNew(array $attributes = []): Model
    {
        $modelClass = $this->getModelClass();
        return new $modelClass($attributes);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getById($id): ?Model
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::findOrFail($id);
        return $model;
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getFirst($column, $operator = null, $value = null, $boolean = 'and'): ?Model
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::where(...func_get_args())->firstOrFail();
        return $model;
    }

    /**
     * @return Model[]
     */
    public function getAll($column, $operator = null, $value = null, $boolean = 'and'): array
    {
        $modelClass = $this->getModelClass();
        $models = $modelClass::where(...func_get_args())->get()->toArray();
        return $models;
    }

    /**
     * @return Model[]
     */
    public function getAllMultipleCriteria(array $criteria): array
    {
        $modelClass = $this->getModelClass();
        $queryBuilder = $modelClass::query();
        foreach ($criteria as $criterion) {
            $queryBuilder = $queryBuilder->where(...$criterion);
        }
        $models = $queryBuilder->get()->toArray();
        return $models;
    }
}
