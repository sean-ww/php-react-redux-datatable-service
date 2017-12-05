<?php

use Illuminate\Database\Capsule\Manager as  DB;

/**
 * Configure the database and boot Eloquent
 */
$capsule = new DB;

// Add the site connection
$capsule->addConnection(
    array(
        'driver'    => 'mysql',
        'host'      => DB_SERVER,
        'database'  => DB_DATABASE,
        'username'  => DB_USERNAME,
        'password'  => DB_PASSWORD,
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => ''
    ),
    "default"
);

$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher(new Illuminate\Container\Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();
