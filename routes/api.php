<?php


    router()->get('/install', function ($request) {
        echo "<pre>";
        $files = glob(App::$base_path . DIR_SEPARATOR . 'install' . DIR_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $file) {
            include $file;
        }
        echo "Installed";
        echo "</pre>";
        echo '<a href="/install?refresh=true">Refresh DB</a>';

    });



    router()->with('/queues', function () {

        router()->get('/?', function ($request, $response) {
            $q = new Queue();
            $q->create("some", 1200);
        });

        router()->respond('GET', '/[:id]', function ($request, $response) {
            // Show a single user
        });

        router()->post('/?', function ($request, $response) {

            $qc = new QueueController($request, $response);
            $qc->save();
        });

    });


    router()->onHttpError(function ($code, $router) {
        switch ($code) {
            default:
                $router->response()->body(
                    'Error! ' . $code
                );
        }
    });