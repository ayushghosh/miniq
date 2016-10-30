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


        public function save()
        {
            $q = new Queue();
            $q->create($this->inputJson['name'],
                $this->inputOrDefault('visibility_timeout', 'queue'),
                $this->inputOrDefault('message_expiration', 'queue'),
                $this->inputOrDefault('maximum_message_size', 'queue'),
                $this->inputOrDefault('delay_seconds', 'queue'),
                $this->inputOrDefault('receive_message_wait_time_seconds', 'queue'),
                $this->inputOrDefault('retries', 'queue'),
                $this->inputOrDefault('retries_delay', 'queue')
            );
        }


    }