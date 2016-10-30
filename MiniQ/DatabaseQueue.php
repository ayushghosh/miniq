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


        public function push($queue_name, $payload, $delay_seconds, $retries)
        {
            $queue = $this->getQueue($queue_name);

            $record = $this->buildJobRecord($queue,
                $this->getValidPayload($queue, $payload),
                $this->getAvailableAt($this->getDelaySeconds($queue, $delay_seconds)),
                $retries);


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

        public function buildJobRecord($queue, $payload, $available_at, $retries)
        {
            return [
                'queue_id' => $queue->id,
                'payload' => $this->getValidPayload($queue, $payload),
                'reserved_at' => null,
                'reserved' => false,
                'expires_at' => null,
                'available_at' => $available_at,
                'created_at' => $this->getTime(),
                'retries' => 0,
                'max_retries' => $this->getMaxRetries($queue, $retries)
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
            if (!$delay_seconds || $delay_seconds == 0) {
                return $queue->delay_seconds;
            }

            return $delay_seconds;
        }

        protected function getMaxRetries($queue, $retries)
        {
            if (!$retries || $retries == 0) {
                return $queue->retries;
            }

            return $retries;
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


        public function daemonJobs()
        {

            $this->releaseExpiredJobs();
            $this->failMaxRetriedJobs();


        }

        public function releaseExpiredJobs()
        {
            $this->connection->beginTransaction();
            try {
                $this->connection->table($this->jobs_table)->lockForUpdate()
                    ->join('queues', 'jobs.queue_id', 'queues.id')
                    ->where('expires_at', '<', $this->getTime())->update([
                        'reserved_at' => null,
                        'expires_at' => null,
                        'reserved' => 0,
                        'available_at' => $this->connection->raw('available_at + queues.retries_delay'),
                    ]);
                $this->connection->commit();
            } catch (Exception $e) {

            }
        }

        public function failMaxRetriedJobs()
        {
            $this->connection->beginTransaction();
            try {
                $jobs = $this->connection->table($this->jobs_table)
                    ->whereRaw($this->jobs_table . '.retries >= ' . $this->jobs_table . '.max_retries')
                    ->select($this->connection->raw('null'), $this->connection->raw('"database" as connection'), 'jobs.queue_id as queue_id', 'jobs.id as job_id', 'jobs.payload as payload', $this->connection->raw('"max_tries" as exception'), $this->connection->raw('NOW() as failed_at'))->toSql();
            } catch (Exception $e) {
                dd($e->getMessage());
            }
            $this->failJob($jobs);
            $this->removeFailedJob();

            $this->connection->commit();

        }

        public function receive($queue_name)
        {
            dd($this->releaseExpiredJobs());
//            dd($this->failMaxRetriedJobs());
            $queue = $this->getQueue($queue_name);

            $this->connection->beginTransaction();

            if ($job = $this->popJob($queue)) {
                $job = $this->markReserved($queue, $job);

                $this->connection->commit();

                return $job;
//                return $this->connection->table($this->jobs_table)->whereIn('id', $jobs)->get();
            }

            $this->connection->commit();


        }

        protected function popJob($queue)
        {
            $job = $this->connection->table($this->jobs_table)
                ->lockForUpdate()
                ->where('queue_id', $queue->id)
                ->where(function ($query) use ($queue) {
                    $this->isAvailable($queue, $query);
//                    $this->isReservedButExpired($query);
                })
                ->orderBy('id', 'asc')
                ->first();

            return $job ? (object)$job : null;
        }

        protected function isAvailable($queue, $query)
        {
            $query->where(function ($query) use ($queue) {
                $query->where('reserved', 0);
                $query->whereNull('reserved_at');
                $query->where('available_at', '<=', $this->getTime());
                $query->whereRaw('retries <= max_retries');
            });
        }

//        protected function isReservedButExpired($query)
//        {
//            $expiration = \Carbon\Carbon::now()->subSeconds(90)->getTimestamp();
//            dd($expiration);
//
//            $query->orWhere(function ($query) use ($expiration) {
//                $query->where('reserved_at', '<=', $expiration);
//            });
//        }

        protected function markReserved($queue, $job)
        {
            $job->retries     = $job->retries + 1;
            $job->reserved_at = $this->getTime();
            $job->expires_at  = $this->getExpiresAt($queue, $job->reserved_at);

            $this->connection->table($this->jobs_table)->where('id', $job->id)->update([
                'reserved' => 1,
                'reserved_at' => $job->reserved_at,
                'retries' => $job->retries,
                'expires_at' => $job->expires_at,
            ]);

            return $job;
        }

        protected function failJob($jobs)
        {
            $sql = 'INSERT into failed_jobs ' . $jobs;
            $this->connection->statement($sql);
        }

        protected function removeFailedJob()
        {
            $jobs = $this->connection->table($this->jobs_table)
                ->whereRaw($this->jobs_table . '.retries >= ' . $this->jobs_table . '.max_retries')->delete();
        }


    }