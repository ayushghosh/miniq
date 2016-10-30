<?php


    if (!function_exists('dd')) {
        function dd($var = null)
        {
            var_dump($var);
            die();
        }
    }


    function env($key, $default = null)
    {
        return isset(App::$env_vars[$key]) ? App::$env_vars[$key] : $default;
    }

    function config($key, $default = null)
    {
        return isset(App::$config_vars[$key]) ? App::$config_vars[$key] : $default;
    }


    function router()
    {
        return App::get('router');
    }

    function db()
    {
        return App::get('db');
    }

    function clean_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }


    function respond($data, $code = 200, $is_json = true)
    {
        if ($is_json) {
            header('Content-Type: application/json', true, $code);
        }
        echo json_encode($data);
        die();
    }

    function respondError($data, $code = 400)
    {
        router()->response()->status(400);

        return respond([
            'object' => 'error',
            'status' => 'error',
            'message' => $data['message'],
            'code' => $code
        ]);
    }




    