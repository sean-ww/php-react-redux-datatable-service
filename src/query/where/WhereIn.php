<?php

namespace datatable\query\where;

/**
 * Where In Clause - A strategy for building Where clause statements.
 */
class WhereIn extends WhereClauseBase
{
    /**
     * Convert an array to a comma separated list of values in quotes.
     *
     * @param array $values
     * @return string
     */
    public function arrayToList($values)
    {
        $list = array();
        foreach ($values as $value) {
            $list[] = $this->addQuotes($value);
        }
        return implode (", ", $list);
    }

    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' IN(('.$this->arrayToList($this->value).')';
    }
}
