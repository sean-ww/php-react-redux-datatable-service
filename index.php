<?php

use \Slim\Slim;
use datatable\Datatable;

/**
 * The starting point for the service application
 */

// Check the request was not meant to serve a static file.
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

// Load composer's autoloader
require 'vendor/autoload.php';

// Use Eloquent
require 'src/database.php';

$app = new Slim();
$dataTable = new Datatable($app);
$app->post('/search', function () use ($app, $dataTable) {
    $dataTable->settings = array(
        'columns' => array(
            'request_id' => '`rs`.`request_id`',
            'user_id' => '`rs`.`user_id`',
            'first_name' => '`rs`.`first_name`',
            'surname' => '`rs`.`surname`',
            'email' => '`rs`.`email`',
            'created_at' => '`rs`.`created_at`',
            'type' => "CASE
                    WHEN `rs`.`type` = 0 THEN 'Add'
                    WHEN `rs`.`type` = 1 THEN 'Amend'
                    WHEN `rs`.`type` = 2 THEN 'Remove'
                    ELSE NULL
                END",
            'system_type' => "CASE
                    WHEN `rs`.`system_type` = 0 THEN 'training'
                    WHEN `rs`.`system_type` = 1 THEN 'staging'
                    WHEN `rs`.`system_type` = 2 THEN 'production'
                    ELSE NULL
                END",
            'actions' => '`rs`.`request_id`',
        ),
        'from' => '
                `requests` AS `rs`
            ',
        'defaultSorOrder' => array('request_id', 'DESC')
    );

    return $dataTable->createTableData();
});
$app->run();
