<?php


    class DatabaseConnector implements ConnectorInterface
    {
        protected $connection;

        public function connect(array $config)
        {
            return new DatabaseQueue($config['connection'], $config['queue_table'], $config['jobs_table']);
        }
    }