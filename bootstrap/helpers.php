<?php


    if (!function_exists('dd')) {
        /**
         * @param null $var
         */
        function dd($var = null)
        {
            var_dump($var);
            die();
        }
    }


    /**
     * @param      $key
     * @param null $default
     * @return null
     */
    function env($key, $default = null)
    {
        return isset(App::$env_vars[$key]) ? App::$env_vars[$key] : $default;
    }

    /**
     * @param      $key
     * @param null $default
     * @return null
     */
    function config($key, $default = null)
    {
        return isset(App::$config_vars[$key]) ? App::$config_vars[$key] : $default;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    function router()
    {
        return App::get('router');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    function db()
    {
        return App::get('db');
    }

    /**
     * @param $data
     * @return string
     */
    function clean_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }


  




    