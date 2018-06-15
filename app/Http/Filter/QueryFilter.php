<?php

namespace App\Http\Filter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
    private $request;
    private $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->getRequestParameters() as $name => $value) {
            if (method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $this->builder;
    }

    protected function getBuilder(): Builder
    {
        return $this->builder;
    }

    private function getRequestParameters()
    {
        return $this->request->all();
    }
}
