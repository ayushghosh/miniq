<?php

//    namespace \MiniQ;

    class Queue
    {

        private $id;
        private $name;
        private $visibility_timeout;
        private $message_expiration;
        private $maximum_message_size;
        private $delay_seconds;
        private $receive_message_wait_time_seconds;
        private $retries;
        private $retries_delay;
        private $messages_available;
        private $messages_in_flight;


        private $connectors;

        /**
         * Queue constructor.
         * @param $id
         */
        public function __construct()
        {
            $this->connectors = QueueConnectors::connectors();
            $this->connection = $this->connectors[config('queue.default')];

        }

        /**
         * @param mixed $name
         */
        public function setName($name)
        {
            if (!$name || $name == '') {
                throw new QueueException("Name is required");
            }
            $this->name = $name;
        }

        /**
         * @param mixed $visibility_timeout
         */
        public function setVisibilityTimeout($visibility_timeout)
        {
            if ($visibility_timeout < 0 || $visibility_timeout > 43200) {
                throw new QueueException("Visibility timeout must be within 0 and 43200");
            }
            $this->visibility_timeout = $visibility_timeout;
        }

        /**
         * @param mixed $message_expiration
         */
        public function setMessageExpiration($message_expiration)
        {
            if ($message_expiration < 0 || $message_expiration > 1209600) {
                throw new QueueException("Message Expiration must be within 0 and 1209600");
            }

            $this->message_expiration = $message_expiration;
        }

        /**
         * @param mixed $maximum_message_size
         */
        public function setMaximumMessageSize($maximum_message_size)
        {
            if (strlen($maximum_message_size) < 0 || strlen($maximum_message_size) > 262144) {
                throw new QueueException("Maximum Message Size must be within 0 and 262144");
            }

            $this->maximum_message_size = $maximum_message_size;
        }

        /**
         * @param mixed $delay_seconds
         */
        public function setDelaySeconds($delay_seconds)
        {
            if ($delay_seconds < 0 || $delay_seconds > 900) {
                throw new QueueException("Delay must be within 0 and 900");
            }

            $this->delay_seconds = $delay_seconds;
        }

        /**
         * @param mixed $receive_message_wait_time_seconds
         */
        public function setReceiveMessageWaitTimeSeconds($receive_message_wait_time_seconds)
        {
            if ($receive_message_wait_time_seconds < 0 || $receive_message_wait_time_seconds > 20) {
                throw new QueueException("Receive Message Wait Time must be within 0 and 20");
            }
            $this->receive_message_wait_time_seconds = $receive_message_wait_time_seconds;
        }

        /**
         * @param mixed $retries
         */
        public function setRetries($retries)
        {
            if ($retries < 1 || $retries > 1000) {
                throw new QueueException("Retries must be within 1 and 1000");
            }

            $this->retries = $retries;
        }

        /**
         * @param mixed $retries_delay
         */
        public function setRetriesDelay($retries_delay)
        {


            $this->retries_delay = $retries_delay;
        }

        private function connection()
        {
            return $this->connectors[config('queue.default')];
        }

        /**
         * Queue constructor.
         * @param $name
         * @param $visibility_timeout
         * @param $message_expiration
         * @param $maximum_message_size
         * @param $delay_seconds
         * @param $receive_message_wait_time_seconds
         * @param $retries
         * @param $retries_delay
         */
        public function create($name, $visibility_timeout = 60, $message_expiration = 1209600, $maximum_message_size = 262144, $delay_seconds = 0, $receive_message_wait_time_seconds = 0, $retries = 1, $retries_delay = 60)
        {
            $this->setName(clean_input($name));
            $this->setVisibilityTimeout(clean_input($visibility_timeout));
            $this->setMessageExpiration(clean_input($message_expiration));
            $this->setMaximumMessageSize(clean_input($maximum_message_size));
            $this->setDelaySeconds(clean_input($delay_seconds));
            $this->setReceiveMessageWaitTimeSeconds(clean_input($receive_message_wait_time_seconds));
            $this->setRetries(clean_input($retries));
            $this->setRetriesDelay(clean_input($retries_delay));

            return $this->createQueue();
        }

        private function createQueue()
        {

            return $this->connection()->create(
                $this->name,
                $this->visibility_timeout,
                $this->message_expiration,
                $this->maximum_message_size,
                $this->delay_seconds,
                $this->receive_message_wait_time_seconds,
                $this->retries,
                $this->retries_delay
            );
        }

        public function index()
        {
            return $this->connection()->index();
        }


        public function push($queue_name, $payload, $delay_seconds, $retries)
        {
            return $this->connection()->push($queue_name, $payload, $delay_seconds,$retries);
        }

        public function receive($queue_name)
        {
            return $this->connection()->receive($queue_name);
        }


        public function daemonJobs()
        {
            $this->connection()->daemonJobs();
        }


    }