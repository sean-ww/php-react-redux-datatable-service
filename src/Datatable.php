<?php

namespace datatable;

use Slim\Slim;
use Valitron\Validator as V;
use Illuminate\Database\Capsule\Manager as DB;
use datatable\query\BuildWhereClauses;
use datatable\query\BuildWhereClauseStatements;

/**
 * Datatable Class - A class for returning Datatable data.
 */
class Datatable
{
    /**
     * Application object.
     *
     * @var Slim $app
     */
    protected $app;

    /**
     * Where clause builder object.
     *
     * @var BuildWhereClauses $whereClauseBuilder
     */
    protected $whereClauseBuilder;

    /**
     * Where clause statement builder object.
     *
     * @var BuildWhereClauseStatements $whereStmtBuilder
     */
    protected $whereStmtBuilder;

    /**
     * Constructor - Get an instance of Slim.
     *
     * @param Slim $app
     */
    public function __construct(Slim $app)
    {
        $this->app = $app;
        $this->whereClauseBuilder = new BuildWhereClauses();
        $this->whereStmtBuilder = new BuildWhereClauseStatements();
    }

    /**
     * Create Table Data - Generate Datatable data.
     *
     * @return array (echo json)
     */
    public function createTableData()
    {
        $searchSuccessArray = array('searchSuccess' => false, 'hasAuth' => true);

        if ($this->settings && $this->settings['columns'] && $this->settings['from']) {
            $validationCheck = $this->validateTableInput();
            if ($validationCheck['isValid']) {
                $results = $this->queryTableData();
                $searchSuccessArray['dataTotalSize'] = $results['dataTotalSize'];
                $searchSuccessArray['data'] = $results['data'];
                $searchSuccessArray['searchSuccess'] = true;
            }
            $searchSuccessArray['errors'] = $validationCheck['errors']; // Add errors to the searchSuccessArray
        }

        if (!$searchSuccessArray['searchSuccess']) {
            $this->app->response->setStatus(400); // set Bad Request status
        }

        header('Content-Type: application/json;  charset=utf-8;');
        echo json_encode($searchSuccessArray);
    }

    /**
     * Validate Table Input - Generic type validation of the POST.
     *
     * @return array Contains isValid (bool) and an errors array
     */
    private function validateTableInput()
    {
        $isValid = false;
        $errors = null;

        // POST Check
        if ($this->app->request->isPost() && $this->app->request->post('limit')) {
            $valitron = new V($this->app->request->post());

            // Always require:
            $valitron->rule(
                'required',
                array(
                    'limit',
                    'offset',
                    'tableSettings',
                )
            );

            // Integer values:
            $valitron->rule(
                'integer',
                array(
                    'limit',
                    'offset',
                )
            );

            // Prevent searching too many
            $valitron->rule('max', 'limit', 1000);

            // Check the sort order
            $valitron->rule('in', 'sortOrder', array('desc', 'asc'));

            // Check the sort name
            $valitron->rule('in', 'sortName', $this->settings['columns']);

            // Determine if the input is valid
            $isValid = $valitron->validate();
        }

        if (!$isValid) {
            $errors = $valitron->errors();
        }

        return array(
            'isValid' => $isValid,
            'errors' => $errors
        );
    }

    /**
     * Build Table Query Parts.
     *
     * @return array The parts of the SQL query
     */
    public function buildTableQueryParts()
    {
        $columns = $this->settings['columns'];
        $from = ' FROM '.$this->settings['from'];

        $groupBy = ''; // This may be added in future

        $sOrder = $this->buildSorting(); // sorting
        $sWhere = $this->buildGlobalSearch(); // global search

        // Clear the clause and statement builders
        $this->whereClauseBuilder->clauses = null;
        $this->whereStmtBuilder->clauses = null;
        $this->whereStmtBuilder->wheres = null;

        // Column search
        $colSearch = $this->buildColumnSearch();
        if ($colSearch) {
            $sWhere = (($sWhere) ? '('.$sWhere . ') AND ' : "") . $colSearch;
        }

        // Build select
        $select = 'SELECT';
        foreach ($columns as $key => $value) {
            $select .= ' '.$value.' AS `'.$key.'`,';
        }
        $select = rtrim($select, ',');

        // prepend sWhere with WHERE if set
        if ($sWhere != "") {
            $sWhere = "WHERE " . $sWhere;
        }

        // If using GROUP BY then switch to HAVING
        $having = '';
        if ($groupBy != '') {
            if ($sWhere != "") {
                $having = "HAVING " . $sWhere;
            }
            $sWhere = ''; // remove sWhere
        }

        return array(
            'select' => $select,
            'from' => $from,
            'sWhere' => $sWhere,
            'groupBy' => $groupBy,
            'having' => $having,
            'sOrder' => $sOrder
        );
    }

    /**
     * Query Table Data - Run the query and return the data.
     *
     * @return array The data and data size
     */
    private function queryTableData()
    {
        // Collect query parts
        $query = $this->buildTableQueryParts();
        // echo($query['sWhere']);

        // Count the data size
        $countRows = DB::select("
            ".$query['select'].",
            COUNT(*) AS `rows`
            ".$query['from']."
            ".$query['sWhere']."
            ".$query['groupBy']."
            ".$query['having']."
        ");
        $dataTotalSize = abs($countRows[0]['rows']);

        // Return the view data
        $data = DB::select("
            ".$query['select']."
            ".$query['from']."
            ".$query['sWhere']."
            ".$query['groupBy']."
            ".$query['having']."
            ".$query['sOrder']."
            LIMIT
                ?, ?
        ", array($this->app->request->post('offset'), $this->app->request->post('limit'))
        );

        return array(
            'dataTotalSize' => $dataTotalSize,
            'data' => $data
        );
    }

    /**
     * Build Sorting - Generate the sorting SQL.
     *
     * @return string The SQL generated
     */
    private function buildSorting()
    {
        $sOrder = '';
        $columns = $this->settings['columns'];
        if ($this->app->request->post('sortName') && $this->app->request->post('sortOrder')) {
            $sortName = $columns[$this->app->request->post('sortName')];
            $sortOrder = $this->app->request->post('sortOrder');
            $sOrder = 'ORDER BY '.$sortName.' '.$sortOrder;
        } elseif ($this->settings['defaultSorOrder']) {
            $sortName = $this->settings['defaultSorOrder'][0];
            $sortOrder = $this->settings['defaultSorOrder'][1];
            $sOrder = 'ORDER BY '.$sortName.' '.$sortOrder;
        }
        return $sOrder;
    }

    /**
     * Get Global Search Object - If defined as searchable and not disabled.
     *
     * @return object A Where Clause object with key, type and value.
     */
    private function getGlobalSearchObject($column, $globalSearchValue)
    {
        $searchObject = null;

        if (!isset($column->searchable) || $column->searchable) {
            if (!isset($column->disableSearchAll) || !$column->disableSearchAll) {
                $searchObject = new \stdClass();
                $searchObject->key = $column->key;
                $searchObject->type = 'like';
                $searchObject->value = $globalSearchValue;
            }
        }

        return $searchObject;
    }

    /**
     * Build Global Search - Generate the global search SQL.
     *
     * @return string The SQL generated
     */
    private function buildGlobalSearch()
    {
        $sWhere = '';
        $globalSearchValue = '';
        $columns = $this->settings['columns'];
        if ($this->app->request->post('searchValue')) {
            $globalSearchValue = $this->app->request->post('searchValue');
        }

        $columnFilters = array();
        $tableSettings = json_decode($this->app->request->post('tableSettings'));
        $tableColumns = $tableSettings->tableColumns;
        if ($globalSearchValue) {
            foreach ($tableColumns as $col) {
                $searchObject = $this->getGlobalSearchObject($col, $globalSearchValue);
                if ($searchObject) {
                    $columnFilters[] = $searchObject;
                }
            }
            if (is_array($columnFilters) && count($columnFilters) > 0) {
                $whereClauses = $this->whereClauseBuilder->buildClauses($columnFilters, $columns);
                $this->whereStmtBuilder->setClauses($whereClauses);
                $sWhere = $this->whereStmtBuilder->buildWhereStatement('OR');
            }
        }
        return $sWhere;
    }

    /**
     * Build Column Search - Generate the column search SQL.
     *
     * @return string The SQL generated
     */
    private function buildColumnSearch()
    {
        $colSearch = '';
        $columns = $this->settings['columns'];
        if ($this->app->request->post('columnFilters')) {
            $columnFilters = json_decode($this->app->request->post('columnFilters'));

            if (is_array($columnFilters) && count($columnFilters) > 0) {
                $whereClauses = $this->whereClauseBuilder->buildClauses($columnFilters, $columns);
                $this->whereStmtBuilder->setClauses($whereClauses);
                $colSearch = $this->whereStmtBuilder->buildWhereStatement();
            }
        }
        return $colSearch;
    }
}
