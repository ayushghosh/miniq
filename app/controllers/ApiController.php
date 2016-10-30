<?php

    /**
     * Created by PhpStorm.
     * User: ayush
     * Date: 30/10/16
     * Time: 11:22 AM
     */
    class ApiController
    {
        protected $request;
        protected $response;
        public    $inputJson;

        /**
         * ApiController constructor.
         * @param $request
         * @param $response
         */
        public function __construct($request, $response)
        {
            $this->request  = $request;
            $this->response = $response;
            $this->inputJson();
            App::bind('request', $request);
            App::bind('response', $response);
        }

        public function inputJson()
        {
            try {
                $array = json_decode($this->request->body(), true);

                if (json_last_error()) {
                    throw new QueueException('Error parsing input');
                }
            } catch (QueueException $e) {
                $e->errorMessage();
            }

            $this->inputJson = $array;
        }


        /**
         * @return mixed
         */
        public function getInputJson()
        {
            return $this->inputJson;
        }


        public static function respond($data, $code = 200, $is_json = true)
        {
            if ($is_json) {
                header('Content-Type: application/json', true, $code);
            }
            echo json_encode($data);
            die();
        }

        public static function respondError($data, $code = 400)
        {
            router()->response()->status(400);

            return respond([
                'object' => 'error',
                'status' => 'error',
                'message' => $data['message'],
                'code' => $code
            ]);
        }


        public function inputOrDefault($field, $key)
        {
            return ( isset($this->inputJson[$field]) ? $this->inputJson[$field]: config('constants.' . $key)[$field]);
        }
    }