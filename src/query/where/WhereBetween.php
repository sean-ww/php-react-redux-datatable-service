<?php

namespace datatable\query\where;

/**
 * Where Between Clause - A strategy for building Where clause statements.
 */
class WhereBetween extends WhereClauseBase
{
    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        $fromValue = $this->addQuotes($this->value->from);
        $toValue = $this->addQuotes($this->value->to);
        return $this->columnName.' BETWEEN '.$fromValue.' AND '.$toValue;
    }
}
