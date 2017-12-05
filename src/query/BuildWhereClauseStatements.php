<?php

namespace datatable\query;

/**
 * Build Where Clause Statements - A class for returning Where statements from Where clause objects.
 */
class BuildWhereClauseStatements
{
    /** @var array $clauses Array of Where clause objects. */
    public $clauses = array();

    /** @var array $wheres Array of Where clause statements. */
    public $wheres;

    /**
     * Set the Where clause objects.
     *
     * @param array $clauses Array of objects.
     */
    public function setClauses(array $clauses)
    {
        $this->clauses = $clauses;
    }

    /**
     * Merge an array of where clauses.
     *
     * @param array $wheres
     * @return array
     */
    public function mergeWheres($wheres)
    {
        $this->wheres = array_merge((array) $this->wheres, (array) $wheres);
        return $this->wheres;
    }

    /**
     * Build Where Clause Dynamically from a Where Clause buildWhereClause method.
     *
     * @param object $clause Where Clause object.
     * @return string
     */
    public function buildWhereClauseDynamically($clause)
    {
        return $clause->buildWhereClause();
    }

    /**
     * Build Where Array - An array of Where clause statements.
     *
     * @return array
     * @throws \Exception
     */
    public function buildWhereArray()
    {
        foreach ($this->clauses as $clause) {
            if (is_a($clause, '\datatable\query\where\WhereClauseInterface')) {
                $whereClause = $this->buildWhereClauseDynamically($clause);
                $this->mergeWheres($whereClause);
                continue;
            }

            throw new \Exception('$clause must be an instance of the WhereClauseInterface');
        }

        return $this->wheres;
    }

    /**
     * Convert Where Array to Statement.
     *
     * @param string $operator
     * @return string
     */
    public function convertWhereArrayToStatement($operator = 'AND')
    {
        return implode(' '.$operator.' ', $this->wheres);
    }

    /**
     * Build a Where clause statement.
     *
     * @param string $operator
     * @return string
     */
    public function buildWhereStatement($operator = 'AND')
    {
        if ($this->buildWhereArray()) {
            return $this->convertWhereArrayToStatement($operator);
        }
        return '';
    }
}
