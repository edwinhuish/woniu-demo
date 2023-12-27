<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class ParseCdpField
{
    /**
     * 筛选条件
     * @var Builder
     */
    protected Builder $filterQuery;
    /**
     * 排除条件
     * @var Builder
     */
    protected Builder $excludeQuery;

    public function __construct()
    {
        $this->filterQuery  = DB::table('customer')->distinct()->select(['customer.id AS customer_id']);
        $this->excludeQuery = $this->filterQuery->clone();
    }

    /**
     * 筛选条件(为空查询所有客户)
     * @param array|null $conditions
     * @return $this
     */
    public function filter(?array $conditions): self
    {
        if ($conditions) {
            $this->buildQuery($conditions, $this->filterQuery, 'and');
        }
        return $this;
    }

    /**
     * 排除条件(为空不排除)
     * @param array|null $conditions
     * @return $this
     */
    public function exclude(?array $conditions): self
    {
        if ($conditions) {
            $this->buildQuery($conditions, $this->excludeQuery, 'not');
        }
        return $this;
    }

    /**
     * 生成查询条件
     * @param array $conditions
     * @param Builder $query
     * @param string $logical
     * @return void
     */
    public function buildQuery(array $conditions, Builder $query, string $logical): void
    {
        if ($conditions['type'] === 'group') {
            $this->handleGroup($conditions, $query, $logical);
        } else {
            $this->handleField($conditions, $query, $logical);
        }
    }

    /**
     * 处理分组
     * @param array $group
     * @param Builder $query
     * @param string $logical
     * @return void
     */

    protected function handleGroup(array $group, Builder $query, string $logical): void
    {
        $children     = $group['children'];
        $groupLogical = $group['logical'];

        $query->where(function () use ($children, $groupLogical, $query) {
            foreach ($children as $child) {
                $this->buildQuery($child, $query, $groupLogical);
            }
        }, null, null, $logical);
    }

    /**
     * 处理字段
     * @param array $field
     * @param Builder $query
     * @param string $logical
     * @return void
     */
    protected function handleField(array $field, Builder $query, string $logical): void
    {
        $table      = $field['table'];
        $columnName = $table . '.' . $field['field'];
        $operator   = $field['operator'];
        $value      = $field['value'];
        $boolean    = $logical === 'and' ? 'and' : 'or';

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

        $this->addJoin($table, $query);
    }

    /**
     * 根据表名添加关联
     * @param string $table
     * @param Builder $query
     * @return void
     */
    protected function addJoin(string $table, Builder $query): void
    {
        // customer 表不需要关联
        if ($table === 'customer') {
            return;
        }

        // 检查是否已经存在这个表的 JOIN
        $joins = collect($query->joins)->pluck('table');
        if ($joins->contains($table)) {
            return;
        }

        $query->leftJoin($table, 'customer.id', '=', $table . '.customer_id');
    }

    public function getSql(): string
    {
        // 筛选sql
        $filterSql = $this->getFullSql($this->filterQuery);
        if (!$this->excludeQuery->wheres) {
            return $filterSql;
        }

        // 排除sql
        $excludeSql = $this->getFullSql($this->excludeQuery);
        return "SELECT * FROM ($filterSql) AS filtered WHERE filtered.customer_id NOT IN ($excludeSql)";
    }

    /**
     * 生成完整的 SQL 语句
     * @param Builder $query
     * @return string
     */
    protected function getFullSql(Builder $query): string
    {
        $sql      = $query->toSql();
        $bindings = $query->getBindings();
        foreach ($bindings as $binding) {
            $sql = preg_replace('/\?/', "'{$binding}'", $sql, 1);
        }
        return $sql;
    }
}
