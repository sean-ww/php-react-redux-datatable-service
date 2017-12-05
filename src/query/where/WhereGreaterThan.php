<?php

namespace datatable\query\where;

/**
 * Where Greater Than Clause - A strategy for building Where clause statements.
 */
class WhereGreaterThan extends WhereClauseBase
{
    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' > '.$this->addQuotes($this->value);
    }
}
