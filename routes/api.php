<?php


    router()->get('/install', function ($request, $response) {
        $files = glob(App::$base_path . DIR_SEPARATOR . 'install' . DIR_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $file) {
            include $file;
        }

        $qc = new QueueController($request, $response);
        ApiController::respondSuccess(["message" => "Installed"]);

//        echo '<a href="/install?refresh=true">Refresh DB</a>';

    });


    router()->with('/queues', function () {

        router()->get('/?', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->index();
        });

        router()->post('/[:queue_name]/jobs', function ($request, $response) {
            // Show a single user
            $qc = new QueueController($request, $response);
            $qc->push($request->queue_name);

        });

        router()->get('/[:queue_name]/jobs', function ($request, $response) {
            // Show a single user
            $qc = new QueueController($request, $response);
            $qc->receive($request->queue_name);

        });

        router()->post('/?', function ($request, $response) {

            $qc = new QueueController($request, $response);
            $qc->create();
        });

    });


    router()->onHttpError(function ($code, $router) {
        switch ($code) {
            default:
                $qc = new QueueController($router->request(), $router->response());
                ApiController::respondError(["message" => "You are lost"], 404);
        }
    });