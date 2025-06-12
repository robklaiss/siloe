<?php

namespace App\Core;

use PDO;
use PDOException;

class QueryBuilder {
    /**
     * The model class name
     */
    protected $model;
    
    /**
     * The database connection
     */
    protected $db;
    
    /**
     * The current query
     */
    protected $query = [
        'select' => ['*'],
        'from' => null,
        'where' => [],
        'orderBy' => [],
        'groupBy' => [],
        'having' => [],
        'joins' => [],
        'limit' => null,
        'offset' => null,
        'bindings' => [
            'where' => [],
            'having' => []
        ]
    ];
    
    /**
     * Create a new query builder instance
     */
    public function __construct($model) {
        $this->model = $model;
        $this->db = $model::getDb();
        $this->query['from'] = $model::getTableName();
    }
    
    /**
     * Set the columns to be selected
     */
    public function select($columns = ['*']) {
        $this->query['select'] = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    /**
     * Add a basic where clause to the query
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and') {
        // If the column is an array, we'll assume it's an array of key-value pairs
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }
        
        // If only 2 arguments are passed, we'll assume the operator is '='
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        // Add the where clause
        $this->query['where'][] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        // Add the value to the bindings
        if ($value !== null) {
            $this->addBinding($value, 'where');
        }
        
        return $this;
    }
    
    /**
     * Add an "or where" clause to the query
     */
    public function orWhere($column, $operator = null, $value = null) {
        return $this->where($column, $operator, $value, 'or');
    }
    
    /**
     * Add a "where in" clause to the query
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false) {
        $type = $not ? 'NotIn' : 'In';
        
        $this->query['where'][] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        foreach ($values as $value) {
            $this->addBinding($value, 'where');
        }
        
        return $this;
    }
    
    /**
     * Add a "where not in" clause to the query
     */
    public function whereNotIn($column, $values, $boolean = 'and') {
        return $this->whereIn($column, $values, $boolean, true);
    }
    
    /**
     * Add a "where null" clause to the query
     */
    public function whereNull($column, $boolean = 'and', $not = false) {
        $type = $not ? 'NotNull' : 'Null';
        
        $this->query['where'][] = [
            'type' => $type,
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }
    
    /**
     * Add a "where not null" clause to the query
     */
    public function whereNotNull($column, $boolean = 'and') {
        return $this->whereNull($column, $boolean, true);
    }
    
    /**
     * Add a "where between" clause to the query
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false) {
        $type = $not ? 'NotBetween' : 'Between';
        
        $this->query['where'][] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        $this->addBinding($values, 'where');
        
        return $this;
    }
    
    /**
     * Add a "where not between" clause to the query
     */
    public function whereNotBetween($column, array $values, $boolean = 'and') {
        return $this->whereBetween($column, $values, $boolean, true);
    }
    
    /**
     * Add a join clause to the query
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner') {
        $join = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        $this->query['joins'][] = $join;
        
        return $this;
    }
    
    /**
     * Add a left join to the query
     */
    public function leftJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'left');
    }
    
    /**
     * Add a right join to the query
     */
    public function rightJoin($table, $first, $operator = null, $second = null) {
        return $this->join($table, $first, $operator, $second, 'right');
    }
    
    /**
     * Add an "order by" clause to the query
     */
    public function orderBy($column, $direction = 'asc') {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $this->query['orderBy'][] = compact('column', 'direction');
        return $this;
    }
    
    /**
     * Add a "group by" clause to the query
     */
    public function groupBy(...$groups) {
        $this->query['groupBy'] = array_merge(
            $this->query['groupBy'],
            $groups
        );
        
        return $this;
    }
    
    /**
     * Add a "having" clause to the query
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and') {
        // If only 2 arguments are passed, we'll assume the operator is '='
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query['having'][] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        $this->addBinding($value, 'having');
        
        return $this;
    }
    
    /**
     * Set the "limit" value of the query
     */
    public function limit($value) {
        $this->query['limit'] = $value;
        return $this;
    }
    
    /**
     * Set the "offset" value of the query
     */
    public function offset($value) {
        $this->query['offset'] = $value;
        return $this;
    }
    
    /**
     * Execute the query as a "select" statement
     */
    public function get($columns = ['*']) {
        if (!empty($columns)) {
            $this->select($columns);
        }
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->model);
    }
    
    /**
     * Execute the query and get the first result
     */
    public function first($columns = ['*']) {
        $results = $this->limit(1)->get($columns);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Execute the query and get the count
     */
    public function count($columns = '*') {
        $this->select($this->db->raw("COUNT({$columns}) as aggregate"));
        $result = $this->first();
        return $result ? (int) $result->aggregate : 0;
    }
    
    /**
     * Execute the query and get the sum
     */
    public function sum($column) {
        $this->select($this->db->raw("SUM({$column}) as aggregate"));
        $result = $this->first();
        return $result ? (float) $result->aggregate : 0;
    }
    
    /**
     * Get the SQL representation of the query
     */
    public function toSql() {
        $sql = [];
        
        // SELECT
        $sql[] = 'SELECT ' . implode(', ', $this->query['select']);
        
        // FROM
        $sql[] = 'FROM ' . $this->query['from'];
        
        // JOINS
        foreach ($this->query['joins'] as $join) {
            $sql[] = strtoupper($join['type']) . ' JOIN ' . $join['table'] . 
                     ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
        }
        
        // WHERE
        if (!empty($this->query['where'])) {
            $sql[] = 'WHERE ' . $this->compileWheres($this->query['where']);
        }
        
        // GROUP BY
        if (!empty($this->query['groupBy'])) {
            $sql[] = 'GROUP BY ' . implode(', ', $this->query['groupBy']);
        }
        
        // HAVING
        if (!empty($this->query['having'])) {
            $sql[] = 'HAVING ' . $this->compileWheres($this->query['having'], 'having');
        }
        
        // ORDER BY
        if (!empty($this->query['orderBy'])) {
            $orderBys = [];
            foreach ($this->query['orderBy'] as $order) {
                $orderBys[] = $order['column'] . ' ' . strtoupper($order['direction']);
            }
            $sql[] = 'ORDER BY ' . implode(', ', $orderBys);
        }
        
        // LIMIT and OFFSET
        if ($this->query['limit'] !== null) {
            $sql[] = 'LIMIT ' . (int) $this->query['limit'];
            
            if ($this->query['offset'] !== null) {
                $sql[] = 'OFFSET ' . (int) $this->query['offset'];
            }
        }
        
        return implode(' ', $sql);
    }
    
    /**
     * Compile the where clauses
     */
    protected function compileWheres(array $wheres, $type = 'where') {
        $sql = [];
        
        foreach ($wheres as $where) {
            $method = 'compileWhere' . $where['type'];
            
            if (method_exists($this, $method)) {
                $sql[] = $where['boolean'] . ' ' . $this->$method($where, $type);
            }
        }
        
        if (!empty($sql)) {
            $sql[0] = ltrim($sql[0], 'and ');
            $sql[0] = ltrim($sql[0], 'or ');
        }
        
        return implode(' ', $sql);
    }
    
    /**
     * Compile a basic where clause
     */
    protected function compileWhereBasic($where, $type) {
        $value = $this->getBinding($where['value'], $type);
        return $where['column'] . ' ' . $where['operator'] . ' ' . $value;
    }
    
    /**
     * Compile a "where in" clause
     */
    protected function compileWhereIn($where, $type) {
        $values = array_map(function($value) use ($type) {
            return $this->getBinding($value, $type);
        }, $where['values']);
        
        return $where['column'] . ' IN (' . implode(', ', $values) . ')';
    }
    
    /**
     * Compile a "where not in" clause
     */
    protected function compileWhereNotIn($where, $type) {
        $values = array_map(function($value) use ($type) {
            return $this->getBinding($value, $type);
        }, $where['values']);
        
        return $where['column'] . ' NOT IN (' . implode(', ', $values) . ')';
    }
    
    /**
     * Compile a "where null" clause
     */
    protected function compileWhereNull($where) {
        return $where['column'] . ' IS NULL';
    }
    
    /**
     * Compile a "where not null" clause
     */
    protected function compileWhereNotNull($where) {
        return $where['column'] . ' IS NOT NULL';
    }
    
    /**
     * Compile a "where between" clause
     */
    protected function compileWhereBetween($where, $type) {
        $min = $this->getBinding($where['values'][0], $type);
        $max = $this->getBinding($where['values'][1], $type);
        return $where['column'] . ' BETWEEN ' . $min . ' AND ' . $max;
    }
    
    /**
     * Add a binding to the query
     */
    protected function addBinding($value, $type = 'where') {
        $this->query['bindings'][$type][] = $value;
    }
    
    /**
     * Get the bindings for the query
     */
    public function getBindings($type = null) {
        if ($type === null) {
            return array_merge(
                $this->query['bindings']['where'],
                $this->query['bindings']['having']
            );
        }
        
        return $this->query['bindings'][$type] ?? [];
    }
    
    /**
     * Get a binding value with the appropriate placeholder
     */
    protected function getBinding($value, $type) {
        if ($value instanceof \DateTime) {
            return $this->db->quote($value->format('Y-m-d H:i:s'));
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        
        if (is_null($value)) {
            return 'NULL';
        }
        
        return $this->db->quote($value);
    }
    
    /**
     * Add an array of where clauses to the query
     */
    protected function addArrayOfWheres($column, $boolean) {
        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->where(...array_values($value));
            } else {
                $this->where($key, '=', $value, $boolean);
            }
        }
        
        return $this;
    }
    
    /**
     * Handle dynamic method calls into the query builder
     */
    public function __call($method, $parameters) {
        if (method_exists($this->model, $scope = 'scope' . ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
    
    /**
     * Call the given scope on the model
     */
    protected function callScope(array $scope, array $parameters) {
        array_unshift($parameters, $this);
        return $scope(...$parameters) ?? $this;
    }
}
