<?php


    /**
     * Class DatabaseQueue
     */
    class DatabaseQueue
    {
        /**
         * @var
         */
        protected $connection;
        /**
         * @var
         */
        protected $queue_table;
        /**
         * @var
         */
        protected $jobs_table;

        /**
         * @var
         */
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


        /**
         * @param $name
         * @param $visibility_timeout
         * @param $message_expiration
         * @param $maximum_message_size
         * @param $delay_seconds
         * @param $receive_message_wait_time_seconds
         * @param $retries
         * @param $retries_delay
         * @return mixed
         */
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

        /**
         * @return array
         */
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


        /**
         * @param $queue_name
         * @param $payload
         * @param $delay_seconds
         * @param $retries
         * @return mixed
         */
        public function push($queue_name, $payload, $delay_seconds, $retries)
        {
            $queue = $this->getQueue($queue_name);

            $record = $this->buildJobRecord($queue,
                $this->getValidPayload($queue, $payload),
                $this->getAvailableAt($this->getDelaySeconds($queue, $delay_seconds)),
                $retries);


            return $this->connection->table($this->jobs_table)->insertGetId($record);
        }

        /**
         * @param        $identifier
         * @param string $type
         */
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

        /**
         * @param $queue
         * @param $payload
         * @param $available_at
         * @param $retries
         * @return array
         */
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

        /**
         * @param $queue
         * @param $payload
         */
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

        /**
         * @return int
         */
        protected function getTime()
        {
            return \Carbon\Carbon::now()->getTimestamp();
        }

        /**
         * @param $queue
         * @param $delay_seconds
         * @return mixed
         */
        protected function getDelaySeconds($queue, $delay_seconds)
        {
            if (!$delay_seconds || $delay_seconds == 0) {
                return $queue->delay_seconds;
            }

            return $delay_seconds;
        }

        /**
         * @param $queue
         * @param $retries
         * @return mixed
         */
        protected function getMaxRetries($queue, $retries)
        {
            if (!$retries || $retries == 0) {
                return $queue->retries;
            }

            return $retries;
        }


        /**
         * @param $delay
         * @return int
         */
        protected function getAvailableAt($delay)
        {
            $availableAt = $delay instanceof DateTime ? $delay : \Carbon\Carbon::now()->addSeconds($delay);

            return $availableAt->getTimestamp();
        }

        /**
         * @param $queue
         * @param $availableAt
         * @return mixed
         */
        protected function getExpiresAt($queue, $availableAt)
        {
            return ($availableAt + $queue->visibility_timeout);
        }


        /**
         * @param $queue_name
         * @return mixed|null|object
         */
        public function receive($queue_name)
        {
            $queue = $this->getQueue($queue_name);

            $this->connection->beginTransaction();

            if ($job = $this->popJob($queue)) {
                $job = $this->markReserved($queue, $job);

                $this->connection->commit();

                return $job;
            }

            $this->connection->commit();


        }

        /**
         * @param $queue
         * @return null|object
         */
        protected function popJob($queue)
        {
            $job = $this->connection->table($this->jobs_table)
                ->lockForUpdate()
                ->where('queue_id', $queue->id)
                ->where(function ($query) use ($queue) {
                    $this->isAvailable($queue, $query);
                })
                ->orderBy('id', 'asc')
                ->first();

            return $job ? (object)$job : null;
        }

        /**
         * @param $queue
         * @param $query
         */
        protected function isAvailable($queue, $query)
        {
            $query->where(function ($query) use ($queue) {
                $query->where('reserved', 0);
                $query->whereNull('reserved_at');
                $query->where('available_at', '<=', $this->getTime());
                $query->whereRaw('retries <= max_retries');
            });
        }

        /**
         * @param $queue
         * @param $job
         * @return mixed
         */
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

        /**
         * @param $jobs
         */
        protected function failJob($jobs)
        {
            $sql = 'INSERT into failed_jobs ' . $jobs;
            $this->connection->statement($sql);
        }

        /**
         *
         */
        protected function removeFailedJob()
        {
            $jobs = $this->connection->table($this->jobs_table)
                ->whereRaw($this->jobs_table . '.retries >= ' . $this->jobs_table . '.max_retries')->delete();
        }


        /**
         * @param $queue_name
         * @param $job_id
         * @return string
         */
        public function deleteJob($queue_name, $job_id)
        {
            $queue = $this->getQueue($queue_name);

            try {
                $job = $this->connection->table($this->jobs_table)->where('queue_id', $queue->id)->where('id', $job_id)->delete();
                if ($job) {
                    $job->delete();

                }

                return $job;

            } catch (Exception $e) {
                return $e->getMessage();
            }


        }


        /**
         * @param $queue_name
         * @param $job_id
         * @param $timeout
         * @return array|string
         */
        public function updateVisibilityTimeout($queue_name, $job_id, $timeout)
        {
            $queue = $this->getQueue($queue_name);

            try {
                $job = $this->connection->table($this->jobs_table)->where('queue_id', $queue->id)->where('id', $job_id)->first();

                if ($job) {
                    if ($job->reserved == 1) {
                        $job = $this->connection->table($this->jobs_table)->where('queue_id', $queue->id)->where('id', $job_id)->update([
                            'expires_at' => $this->connection->raw($this->getTime() + $timeout)
                        ]);

                        return ['message' => 'Timeout updated', 'status' => 'success'];

                    } else {
                        return ['message' => 'Job not in flight', 'status' => 'error'];
                    }
                }

                return ['message' => 'Job not found', 'status' => 'error'];

            } catch (Exception $e) {
                return $e->getMessage();
            }


        }


        /**
         *
         */
        public function daemonJobs()
        {

            $this->releaseExpiredJobs();
            $this->failMaxRetriedJobs();


        }

        /**
         *
         */
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

        /**
         *
         */
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


    }