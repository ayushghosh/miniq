<?php


    /**
     * Class QueueConnectors
     */
    class QueueConnectors
    {

        /**
         * @var array
         */
        private static $drivers = [];


        /**
         * @return array
         */
        public static function connectors()
        {
            foreach (config('queue.drivers') as $driver) {
                $connector_add = 'add' . (studly_case($driver) . 'Connector');
                self::$connector_add($driver);
            }


            return self::$drivers;
        }


        /**
         * @param $driver
         * @throws Exception
         */
        protected static function addDatabaseConnector($driver)
        {
            $db_connector = new DatabaseConnector();
            self::$drivers[$driver] = $db_connector->connect([
                'connection' => App::get('db'),
                'queue_table' => config('queue.connections')[$driver]['queue_table'],
                'jobs_table' => config('queue.connections')[$driver]['jobs_table']
            ]);
        }

    }