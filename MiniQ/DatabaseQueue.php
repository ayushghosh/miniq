<?php


    class DatabaseQueue
    {
        protected $connection;
        protected $queue_table;
        protected $jobs_table;

        protected $queue;

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
                    throw new QueueException('Queue name already exist', 1);
                }

                return $this->connection->table($this->queue_table)->insertGetId([
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

        public function index()
        {
            try {
                $queues = ($this->connection->table($this->queue_table)->get());

            } catch (QueueException $e) {
                $e->errorMessage();
            }

            if ($queues) {
                return ($queues);
            }

            return [];
        }


        public function push($queue_name, $payload, $delay_seconds)
        {
            $queue = $this->getQueue($queue_name);

            $record = $this->buildJobRecord($queue,
                $this->getValidPayload($queue, $payload),
                $this->getAvailableAt($this->getDelaySeconds($queue, $delay_seconds)),
                $attempts = 0);


            return $this->connection->table($this->jobs_table)->insertGetId($record);
        }

        private function getQueue($identifier, $type = 'name')
        {
            try {
                $queue = $this->connection->table($this->queue_table)->where($type, $identifier)->first();
                if (!$queue) {
                    throw new QueueException('Invalid queue identifier');
                }
            } catch (QueueException $e) {
                return $e->errorMessage();
            }

            return $queue;


        }

        public function buildJobRecord($queue, $payload, $available_at, $attempts)
        {
            return [
                'queue_id' => $queue->id,
                'payload' => $this->getValidPayload($queue, $payload),
                'reserved_at' => null,
                'reserved' => false,
                'expires_at' => null,
                'available_at' => $available_at,
                'created_at' => $this->getTime(),
                'attempts' => $attempts
            ];

        }

        protected function getValidPayload($queue, $payload)
        {
            try {
                if (strlen($payload) > $queue->maximum_message_size) {
                    throw new QueueException('Payload size is greater then acceptable.');
                }
            } catch (QueueException $e) {
                return $e->errorMessage();
            }

            return $payload;

        }

        protected function getTime()
        {
            return \Carbon\Carbon::now()->getTimestamp();
        }

        protected function getDelaySeconds($queue, $delay_seconds)
        {
            if(!$delay_seconds || $delay_seconds == 0)
            {
                return $queue->delay_seconds;
            }
            return $delay_seconds;
        }

        protected function getAvailableAt($delay)
        {
            $availableAt = $delay instanceof DateTime ? $delay : \Carbon\Carbon::now()->addSeconds($delay);

            return $availableAt->getTimestamp();
        }

        protected function getExpiresAt($queue, $availableAt)
        {
            return ($availableAt + $queue->visibility_timeout);
        }

        public function releaseExpiredJobs()
        {
            try{
                $this->connection->table($this->jobs_table)->where('expires_at','<',$this->getTime())->update([
                    'reserved_at' =>null,
                    'expires_at' => null,
                    'reserved' => 0
                ]);
            }
            catch(Exception $e)
            {

            }
        }


    }