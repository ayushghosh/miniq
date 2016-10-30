<?php


    /**
     * Class App
     */
    class App
    {

        /**
         * @var
         */
        public static $base_path;
        /**
         * @var
         */
        public static $config_path;
        /**
         * @var array
         */
        public static $env_vars    = [];
        /**
         * @var array
         */
        public static $config_vars = [];

        /**
         * @var array
         */
        protected static $bindings = [];

        /**
         * @var
         */
        public static $instance;


        /**
         * @return mixed
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
                self::init();
            }

            return static::$instance;
        }

        /**
         *
         */
        private static function init()
        {
            self::$base_path   = self::get_base_path();
            self::$config_path = self::$base_path . DIR_SEPARATOR . 'config';
            self::load_env_vars();
            self::load_config_vars();
        }

        /**
         * @param $key
         * @param $value
         */
        public static function bind($key, $value)
        {
            static::$bindings[$key] = $value;
        }

        /**
         * @param $key
         * @return mixed
         * @throws Exception
         */
        public static function get($key)
        {
            if (!array_key_exists($key, static::$bindings)) {
                throw new Exception("No {$key} is binded.");
            }

            return static::$bindings[$key];
        }

        /**
         * @return mixed
         */
        private static function get_base_path()
        {
            $pathinfo = pathinfo(getcwd());

            return $pathinfo['dirname'];
        }

        /**
         *
         */
        private static function load_env_vars()
        {
            self::$env_vars = require self::$base_path . DIR_SEPARATOR . 'env.php';
        }

        /**
         *
         */
        private static function load_config_vars()
        {
            $files   = glob(self::$config_path . DIR_SEPARATOR . '*.php');
            $configs = [];
            foreach ($files as $file) {
                $file_name      = basename($file, '.php');
                $config_in_file = require($file);
                foreach ($config_in_file as $config_name => $config_value) {
                    $configs[$file_name . '.' . $config_name] = $config_value;
                }
                $config_in_file = [];

            }
            self::$config_vars = $configs;
        }


        /**
         *
         */
        public static function load_routes()
        {
            $files   = glob(self::$base_path . DIR_SEPARATOR . 'routes' . DIR_SEPARATOR . '*.php');
            foreach ($files as $file) {
                require $file;
            }
        }

    }
