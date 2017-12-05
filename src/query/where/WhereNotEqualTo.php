<?php

namespace datatable\query\where;

/**
 * Where Not Equal To Clause - A strategy for building Where clause statements.
 */
class WhereNotEqualTo extends WhereClauseBase
{
    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' != '.$this->addQuotes($this->value);
    }
}
