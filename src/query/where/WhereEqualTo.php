<?php

namespace datatable\query\where;

/**
 * Where Equal To Clause - A strategy for building Where clause statements.
 */
class WhereEqualTo extends WhereClauseBase
{
    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' = '.$this->addQuotes($this->value);
    }
}
