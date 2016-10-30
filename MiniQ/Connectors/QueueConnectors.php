<?php


    class QueueConnectors
    {

        private static $drivers = [];

        /**
         * QueueConnectors constructor.
         * @param $connector
         */


        public static function connectors()
        {
            foreach (config('queue.drivers') as $driver) {
                $connector_add = 'add' . (studly_case($driver) . 'Connector');
                self::$connector_add($driver);
            }


            return self::$drivers;
        }


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