<?php


    class App
    {

        public static $base_path;
        public static $config_path;
        public static $env_vars    = [];
        public static $config_vars = [];

        protected static $bindings = [];

        public static $instance;


        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
                self::init();
            }

            return static::$instance;
        }

        private static function init()
        {
            self::$base_path   = self::get_base_path();
            self::$config_path = self::$base_path . DIR_SEPARATOR . 'config';
            self::load_env_vars();
            self::load_config_vars();
        }

        public static function bind($key, $value)
        {
            static::$bindings[$key] = $value;
        }

        public static function get($key)
        {
            if (!array_key_exists($key, static::$bindings)) {
                throw new Exception("No {$key} is binded.");
            }

            return static::$bindings[$key];
        }

        private static function get_base_path()
        {
            $pathinfo = pathinfo(getcwd());

            return $pathinfo['dirname'];
        }

        private static function load_env_vars()
        {
            self::$env_vars = require self::$base_path . DIR_SEPARATOR . 'env.php';
        }

        private static function load_config_vars()
        {
            $files   = glob(self::$config_path . DIR_SEPARATOR . '*.php');
            $configs = [];
            foreach ($files as $file) {
                $file_name      = basename($file, '.php');
                $config_in_file = require($file);
                foreach ($config_in_file as $config_name => $config_value) {
//                    var_dump($file_name.'.'.$config_name);
                    $configs[$file_name . '.' . $config_name] = $config_value;
                }
                $config_in_file = [];

            }
            self::$config_vars = $configs;
        }


        public static function load_routes()
        {
            $files   = glob(self::$base_path . DIR_SEPARATOR . 'routes' . DIR_SEPARATOR . '*.php');
            $configs = [];
            foreach ($files as $file) {
                require $file;
            }
        }

    }
