<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class Parser
{
    /**
     * 筛选条件
     * @var Builder
     */
    protected Builder $query;

    public function __construct(array $data)
    {

        $this->query  = DB::table($data['table'])->distinct()->select(['customer.id AS customer_id']);

        $this->parseConditions($this->query, $data['children'], $data['logical']);

    }

    public static function parseQuery(array $data)
    {
        return new static($data);
    }

    protected function parseConditions(Builder $query, ?array $conditions, ?string $logical = 'and')
    {
        if(!$conditions) {
            return;
        }

        $logical = $logical === 'or' ? 'or' : 'and';

        foreach($conditions as $condition) {
            $this->parseCondition($query, $condition, $logical);
        }

    }


    protected function parseCondition(Builder $query, array $condition, string $boolean)
    {
        if($condition['type'] === 'group') {

            $query->where(function ($q) use ($condition) {
                $this->parseConditions($q, $condition['children'], $condition['logical']);
            });

            return;
        }


        $table      = $condition['table'];
        $columnName = $table . '.' . $condition['field'];
        $operator   = $condition['operator'] ?: '=';
        $value      = $condition['value'];

        $this->addJoinIfNeed($table);

        switch ($operator) {
            case 'in':
                $query->whereIn($columnName, $value, $boolean);
                break;
            case 'not in':
                $query->whereNotIn($columnName, $value, $boolean);
                break;
            case 'between':
                $query->whereBetween($columnName, $value, $boolean);
                break;
            case 'not between':
                $query->whereNotBetween($columnName, $value, $boolean);
                break;
            case 'like':
                $query->where($columnName, 'like', '%' . $value . '%', $boolean);
                break;
            case 'is null':
                $query->whereNull($columnName, $boolean);
                break;
            case 'is not null':
                $query->whereNotNull($columnName, $boolean);
                break;
            default:
                $query->where($columnName, $operator, $value, $boolean);
                break;
        }


    }

    protected function addJoinIfNeed(string $table)
    {
        if(!$table) {
            return;
        }

        if($table === $this->query->from) {
            return;
        }

        if(collect($this->query->joins)->pluck('table')->contains($table)) {
            return;
        }

        $from = $this->query->from;

        $this->query->leftJoin($table, "{$from}.id", '=', "{$table}.{$from}_id");
    }

    public function toRawSql(): string
    {
        return $this->query->toRawSql();
    }

    public function toString(): string
    {
        return $this->toRawSql();
    }

    public function __toString()
    {
        return $this->toString();
    }
}
