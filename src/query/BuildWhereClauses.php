<?php

namespace datatable\query;

/**
 * Build Where Clauses - A class for returning an array of Where clause objects from a json object.
 */
class BuildWhereClauses
{
    /** @var array $clauses Array of Where clause objects. */
    public $clauses;

    /**
     * Build each Where clause object and add them to an array.
     *
     * @param array $columnFilters Array of json objects
     * @param array $columns Array of column name mappings
     * @return array
     */
    public function buildClauses($columnFilters, $columns)
    {
        foreach ($columnFilters as $columnSearch) {
            $columnName = $this->getColumnName($columnSearch->key, $columns);

            $whereClause = $this->prepareWhereClause(
                $columnSearch->type,
                $columnName,
                $columnSearch->value
            );

            if ($whereClause) {
                $this->clauses[] = $whereClause;
            }
        }
        return $this->clauses;
    }

    /**
     * Get the correct column name.
     *
     * @param string $key Column alias.
     * @param array $columns Array of alaises mapped to their real column names.
     * @return string
     */
    public function getColumnName($key, $columns)
    {
        return $columns[$key];
    }

    /**
     * Prepare the Where Clause object.
     *
     * @param string $type Where type
     * @param string $columnName Column name mapping
     * @param mixed $value The value to be passed to the Where Clause object
     * @return object
     */
    public function prepareWhereClause($type, $columnName, $value)
    {
        $whereClause = null;
        switch ($type) {
            case 'like':
                $whereClause = new where\WhereLike($columnName, $value);
                break;
            case 'in':
                $whereClause = new where\WhereIn($columnName, $value);
                break;
            case 'between':
                $whereClause = new where\WhereBetween($columnName, $value);
                break;
            case 'eq':
                $whereClause = new where\WhereEqualTo($columnName, $value);
                break;
            case 'gt':
                $whereClause = new where\WhereGreaterThan($columnName, $value);
                break;
            case 'gteq':
                $whereClause = new where\WhereGreaterThanOrEqualTo($columnName, $value);
                break;
            case 'lt':
                $whereClause = new where\WhereLessThan($columnName, $value);
                break;
            case 'lteq':
                $whereClause = new where\WhereLessThanOrEqualTo($columnName, $value);
                break;
            case 'nteq':
                $whereClause = new where\WhereNotEqualTo($columnName, $value);
                break;
        }
        return $whereClause;
    }
}
