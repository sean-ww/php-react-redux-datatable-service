<?php

namespace datatable\query\where;

/**
 * Where Like Clause - A strategy for building Where clause statements.
 */
class WhereLike extends WhereClauseBase
{
    /**
     * escapeLike - Escpae a LIKE statement.
     *
     * @param string $string a string value
     * @return string
     */
    public function escapeLike($string)
    {
        $search = array('%', '_');
        $replace   = array('\%', '\_');
        $string = '%'.str_replace($search, $replace, $string).'%';
        $string = $this->addQuotes($string);
        return $string;
    }

    /**
     * Build Where Clause
     *
     * @return string
     */
    public function buildWhereClause()
    {
        return $this->columnName.' LIKE '.$this->escapeLike($this->value);
    }
}
