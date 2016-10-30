<?php

   
    class QueueController extends ApiController
    {



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
            $response = $q->push($queue_name,
                self::$inputJson['payload'],
                self::inputOrDefault('delay_seconds', 'queue'),
                self::inputOrDefault('retries', 'queue'));

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
                    'retries' => $response->retries
                ];

                return self::respondObject(
                    $data, 'queue.job.receive');
            }

            return self::respondError("Empty Queue", 200);
        }


        public function deleteJob($queue_name, $job_id)
        {
            $q        = new Queue();
            $response = $q->deleteJob($queue_name, $job_id);

            if ($response) {
                return self::respondSuccess("Job deleted");
            } else {
                return self::respondError("Job couldn't be deleted");
            }
        }


        public function updateVisibilityTimeout($queue_name, $job_id)
        {
            $q        = new Queue();
            $response = $q->updateVisibilityTimeout($queue_name, $job_id, self::inputOrDefault('visibility_timeout', 'queue'));
            if ($response['status'] == 'success') {
                return self::respondSuccess($response['message']);
            } else {
                return self::respondError($response['message']);
            }
        }


    }