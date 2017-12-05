<?php

namespace datatable\query\where;

/**
 * Where Less Than Clause - A strategy for building Where clause statements.
 */
class WhereLessThan extends WhereClauseBase
{
    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' < '.$this->addQuotes($this->value);
    }
}
