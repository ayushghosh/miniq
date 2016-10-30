<?php
    
    include_once 'app.php';
    

    $router = new \Klein\Klein();


    App::bind('router', $router);

    App::load_routes();

    $router->dispatch();



