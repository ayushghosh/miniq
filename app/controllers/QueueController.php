<?php


    /**
     * Class QueueController
     */
    class QueueController extends ApiController
    {


        /**
         * QueueController constructor.
         * @param $request
         * @param $response
         */
        public function __construct($request, $response)
        {
            parent::__construct($request, $response);

        }

        /**
         *
         */
        public function index()

        {
            $q = new MiniQ();

            return self::respondObject($q->index(), 'queues.index');
        }

        /**
         *
         */
        public function create()
        {
            $q = new MiniQ();
            $x = $q->create(self::$inputJson['name'],
                self::inputOrDefault('visibility_timeout', 'queue'),
                self::inputOrDefault('message_expiration', 'queue'),
                self::inputOrDefault('maximum_message_size', 'queue'),
                self::inputOrDefault('delay_seconds', 'queue'),
                self::inputOrDefault('receive_message_wait_time_seconds', 'queue'),
                self::inputOrDefault('retries', 'queue'),
                self::inputOrDefault('retries_delay', 'queue')
            );

            return self::respondSuccess('Queue Added');
        }


        /**
         * @param $queue_name
         */
        public function push($queue_name)
        {
            $q        = new MiniQ();
            $response = $q->push($queue_name,
                self::$inputJson['payload'],
                self::inputOrDefault('delay_seconds', 'queue'),
                self::inputOrDefault('retries', 'queue'));

            return self::respondObject([
                'message' => 'Job queued',
                'job_id' => $response
            ], 'queue.job.pushed');
        }


        /**
         * @param $queue_name
         */
        public function receive($queue_name)
        {
            $q        = new MiniQ();
            $response = $q->receive($queue_name);

            if ($response) {
                $data = [
                    'job_id' => $response->id,
                    'payload' => $response->payload,
                    'retries' => $response->retries
                ];

                return self::respondObject(
                    $data, 'queue.job.receive');
            }

            return self::respondError("Empty Queue", 200);
        }


        /**
         * @param $queue_name
         * @param $job_id
         */
        public function deleteJob($queue_name, $job_id)
        {
            $q        = new MiniQ();
            $response = $q->deleteJob($queue_name, $job_id);

            if ($response['status'] == 'success') {
                return self::respondSuccess($response['message']);
            } else {
                return self::respondError($response['message']);
            }
        }


        /**
         * @param $queue_name
         * @param $job_id
         */
        public function updateVisibilityTimeout($queue_name, $job_id)
        {
            $q        = new MiniQ();
            $response = $q->updateVisibilityTimeout($queue_name, $job_id, self::inputOrDefault('visibility_timeout', 'queue'));
            if ($response['status'] == 'success') {
                return self::respondSuccess($response['message']);
            } else {
                return self::respondError($response['message']);
            }
        }


    }