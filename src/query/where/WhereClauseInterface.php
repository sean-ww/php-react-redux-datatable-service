<?php

namespace datatable\query\where;

/**
 * An interface for defining objects that build Where clause statements.
 */
interface WhereClauseInterface
{
    /**
     * Build Where clauses
     *
     * @return string
     */
    public function buildWhereClause();
}
