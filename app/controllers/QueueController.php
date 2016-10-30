<?php

    /**
     * Created by PhpStorm.
     * User: ayush
     * Date: 30/10/16
     * Time: 11:13 AM
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

        public function index()

        {
            $q = new Queue();

            return self::respondObject($q->index(), 'queues.index');
        }

        public function create()
        {
            $q = new Queue();
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


        public function push($queue_name)
        {
            $q        = new Queue();
            $response = $q->push($queue_name, self::$inputJson['payload'], self::inputOrDefault('delay_seconds', 'queue'));

            return self::respondObject([
                'message' => 'Job queued',
                'job_id' => $response
            ], 'queue.job.pushed');
        }


        public function receive($queue_name)
        {
            $q        = new Queue();
            $response = $q->receive($queue_name);

            if ($response) {
                $data = [
                    'job_id' => $response->id,
                    'payload' => $response->payload,
                    'attempts' => $response->attempts
                ];
                return self::respondObject(
                    $data, 'queue.job.receive');
            }

            return self::respondError("Empty Queue", 200);
        }


    }