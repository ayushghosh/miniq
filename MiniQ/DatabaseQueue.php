<?php


    class DatabaseQueue
    {
        protected $connection;
        protected $queue_table;
        protected $jobs_table;

        /**
         * DatabaseQueue constructor.
         * @param $connection
         * @param $queue_table
         * @param $jobs_table
         */
        public function __construct($connection, $queue_table, $jobs_table)
        {
            $this->connection  = $connection;
            $this->queue_table = $queue_table;
            $this->jobs_table  = $jobs_table;
        }


        public function create($name, $visibility_timeout, $message_expiration, $maximum_message_size, $delay_seconds, $receive_message_wait_time_seconds, $retries, $retries_delay)
        {
            try {
                $exists = $this->connection->table($this->queue_table)->where('name', $name)->first();
                if ($exists) {
                    throw new QueueException('Queue name already exist',1);
                }
                $this->connection->table($this->queue_table)->insert([
                    'name' => $name,
                    'visibility_timeout' => $visibility_timeout,
                    'message_expiration' => $message_expiration,
                    'maximum_message_size' => $maximum_message_size,
                    'delay_seconds' => $delay_seconds,
                    'receive_message_wait_time_seconds' => $receive_message_wait_time_seconds,
                    'retries' => $retries,
                    'retries_delay' => $retries_delay,
                ]);
            } catch (QueueException $e) {
                $e->errorMessage();
            }

        }


    }