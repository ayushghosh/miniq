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

//    dd($router->request()->body());







    //    require_once (App::$base_path.DIR_SEPARATOR.'routes'.DIR_SEPARATOR.'api.php');


    ////    $db->setAsGlobal();
    //    $db->getConnection(config('database.default'))->table('jobs')->get();


    //    dd(DB::table('jobs')->get());
    //    dd(\Illuminate\Support\Facades\DB::table('jobs')->get());




