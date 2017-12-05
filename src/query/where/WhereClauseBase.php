<?php

namespace datatable\query\where;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Where Greater Than Clause - A strategy for building Where clause statements.
 */
abstract class WhereClauseBase implements WhereClauseInterface
{
    /** @var string $columnName */
    protected $columnName;

    /** @var string $value */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $columnName
     * @param mixed $value
     */
    public function __construct($columnName, $value)
    {
        $this->columnName = $columnName;
        $this->value = $value;
    }

    /**
     * Add Quotes.
     *
     * @param mixed $value
     * @return string
     */
    public function addQuotes($value)
    {
        return DB::connection()->getPdo()->quote($value);
    }

    /**
     * Build Where Clause
     *
     * @return string
     */
    abstract public function buildWhereClause();
}
