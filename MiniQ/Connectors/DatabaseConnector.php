<?php


    /**
     * Class DatabaseConnector
     */
    class DatabaseConnector implements ConnectorInterface
    {
        /**
         * @var
         */
        protected $connection;

        /**
         * @param array $config
         * @return DatabaseQueue
         */
        public function connect(array $config)
        {
            return new DatabaseQueue($config['connection'], $config['queue_table'], $config['jobs_table']);
        }
    }