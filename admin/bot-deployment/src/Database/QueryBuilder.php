<?php
/**
 * Query Builder
 *
 * Fluent query builder with parameter binding and security
 *
 * @package BotDeployment\Database
 */

namespace BotDeployment\Database;

use PDO;

class QueryBuilder
{
    /**
     * Database connection
     * @var PDO
     */
    private $connection;

    /**
     * Table name
     * @var string
     */
    private $table;

    /**
     * Select columns
     * @var array
     */
    private $select = ['*'];

    /**
     * Where conditions
     * @var array
     */
    private $where = [];

    /**
     * Bindings
     * @var array
     */
    private $bindings = [];

    /**
     * Join clauses
     * @var array
     */
    private $joins = [];

    /**
     * Order by clauses
     * @var array
     */
    private $orderBy = [];

    /**
     * Group by columns
     * @var array
     */
    private $groupBy = [];

    /**
     * Having conditions
     * @var array
     */
    private $having = [];

    /**
     * Limit
     * @var int|null
     */
    private $limit = null;

    /**
     * Offset
     * @var int|null
     */
    private $offset = null;

    /**
     * Constructor
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set table
     * @param string $table
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set select columns
     * @param array|string $columns
     * @return self
     */
    public function select($columns = ['*']): self
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add where condition
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return self
     */
    public function where(string $column, $operator, $value = null, string $boolean = 'AND'): self
    {
        // If only 2 params, assume = operator
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $placeholder = ':where_' . count($this->bindings);
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'placeholder' => $placeholder,
            'boolean' => $boolean
        ];
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    /**
     * Add OR where condition
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function orWhere(string $column, $operator, $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add where IN condition
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return self
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $placeholder = ':wherein_' . count($this->bindings);
            $placeholders[] = $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $this->where[] = [
            'column' => $column,
            'operator' => 'IN',
            'placeholder' => '(' . implode(',', $placeholders) . ')',
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * Add where NULL condition
     * @param string $column
     * @param bool $not
     * @param string $boolean
     * @return self
     */
    public function whereNull(string $column, bool $not = false, string $boolean = 'AND'): self
    {
        $this->where[] = [
            'column' => $column,
            'operator' => $not ? 'IS NOT NULL' : 'IS NULL',
            'placeholder' => '',
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * Add where NOT NULL condition
     * @param string $column
     * @param string $boolean
     * @return self
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, true, $boolean);
    }

    /**
     * Add join clause
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    /**
     * Add left join clause
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add order by clause
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = $column . ' ' . strtoupper($direction);
        return $this;
    }

    /**
     * Add group by clause
     * @param string|array $columns
     * @return self
     */
    public function groupBy($columns): self
    {
        $this->groupBy = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add having clause
     * @param string $condition
     * @return self
     */
    public function having(string $condition): self
    {
        $this->having[] = $condition;
        return $this;
    }

    /**
     * Set limit
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set offset
     * @param int $offset
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Build SELECT query
     * @return string
     */
    private function buildSelect(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->table;

        // Joins
        foreach ($this->joins as $join) {
            $sql .= sprintf(
                ' %s JOIN %s ON %s %s %s',
                $join['type'],
                $join['table'],
                $join['first'],
                $join['operator'],
                $join['second']
            );
        }

        // Where
        if (!empty($this->where)) {
            $sql .= ' WHERE ';
            $conditions = [];
            foreach ($this->where as $i => $condition) {
                $prefix = $i > 0 ? $condition['boolean'] . ' ' : '';
                $conditions[] = $prefix . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['placeholder'];
            }
            $sql .= implode(' ', $conditions);
        }

        // Group By
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        // Having
        if (!empty($this->having)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->having);
        }

        // Order By
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        // Limit
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        // Offset
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Execute query and get all results
     * @return array
     */
    public function get(): array
    {
        $sql = $this->buildSelect();
        $stmt = $this->connection->prepare($sql);

        foreach ($this->bindings as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Execute query and get first result
     * @return array|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Get count of results
     * @return int
     */
    public function count(): int
    {
        $originalSelect = $this->select;
        $this->select = ['COUNT(*) as count'];

        $result = $this->first();
        $this->select = $originalSelect;

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Insert record
     * @param array $data
     * @return int Last insert ID
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Update records
     * @param array $data
     * @return int Affected rows
     */
    public function update(array $data): int
    {
        $sets = [];
        foreach ($data as $column => $value) {
            $placeholder = ':set_' . $column;
            $sets[] = $column . ' = ' . $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $sets);

        // Where
        if (!empty($this->where)) {
            $sql .= ' WHERE ';
            $conditions = [];
            foreach ($this->where as $i => $condition) {
                $prefix = $i > 0 ? $condition['boolean'] . ' ' : '';
                $conditions[] = $prefix . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['placeholder'];
            }
            $sql .= implode(' ', $conditions);
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($this->bindings as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Delete records
     * @return int Affected rows
     */
    public function delete(): int
    {
        $sql = 'DELETE FROM ' . $this->table;

        // Where
        if (!empty($this->where)) {
            $sql .= ' WHERE ';
            $conditions = [];
            foreach ($this->where as $i => $condition) {
                $prefix = $i > 0 ? $condition['boolean'] . ' ' : '';
                $conditions[] = $prefix . $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['placeholder'];
            }
            $sql .= implode(' ', $conditions);
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($this->bindings as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Execute raw query
     * @param string $sql
     * @param array $bindings
     * @return array
     */
    public function raw(string $sql, array $bindings = []): array
    {
        $stmt = $this->connection->prepare($sql);

        foreach ($bindings as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Begin transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}
