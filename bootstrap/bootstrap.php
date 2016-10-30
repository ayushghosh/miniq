<?php

    use Illuminate\Database\Capsule\Manager as DB;

    define('DIR_SEPARATOR', '/');
    require_once '../vendor/autoload.php';
    require_once 'helpers.php';
    require_once '../app/App.php';
    $app = App::getInstance();


    $db_con = new DB;
    $db_con->addConnection(config('database.connections')[config('database.default')], config('database.default'));
    $db_con = $db_con->getConnection(config('database.default'));


    $router = new \Klein\Klein();


    App::bind('db', $db_con);
    App::bind('router', $router);

    App::load_routes();

    $router->dispatch();



