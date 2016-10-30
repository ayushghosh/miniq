<?php


    router()->get('/install', function ($request, $response) {
        $files = glob(App::$base_path . DIR_SEPARATOR . 'install' . DIR_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $file) {
            include $file;
        }

        $qc = new QueueController($request, $response);
        ApiController::respondSuccess(["message" => "Installed"]);
    });


    router()->with('/queues', function () {

        router()->get('/?', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->index();
        });

        router()->post('/[:queue_name]/jobs', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->push(clean_input($request->queue_name));

        });

        router()->get('/[:queue_name]/jobs', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->receive(clean_input($request->queue_name));

        });

        router()->delete('/[:queue_name]/jobs/[:job_id]', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->deleteJob(clean_input($request->queue_name), clean_input($request->job_id));

        });

        router()->post('/[:queue_name]/jobs/[:job_id]/timeout', function ($request, $response) {
            $qc = new QueueController($request, $response);
            $qc->updateVisibilityTimeout(clean_input($request->queue_name), clean_input($request->job_id));

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