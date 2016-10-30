<?php

    /**
     * Created by PhpStorm.
     * User: ayush
     * Date: 30/10/16
     * Time: 11:22 AM
     */
    class ApiController
    {
        protected static $request;
        protected static $response;
        public static    $inputJson;

        /**
         * ApiController constructor.
         * @param $request
         * @param $response
         */
        public function __construct($request, $response)
        {
            self::$request  = $request;
            self::$response = $response;
            if (in_array($request->method(), ['POST', 'PUT'])) {

                self::inputJson();
            }
            App::bind('request', $request);
            App::bind('response', $response);
        }

        public static function inputJson()
        {
            try {
                $array = json_decode(self::$request->body(), true);

                if (json_last_error()) {
                    throw new QueueException('Error parsing input');
                }
            } catch (QueueException $e) {
                $e->errorMessage();
            }

            self::$inputJson = $array;
        }


        /**
         * @return mixed
         */
        public function getInputJson()
        {
            return self::$inputJson;
        }


        public static function respond($data, $code = 200)
        {
            self::$response->code($code);
            self::$response->json($data);
//            if ($is_json) {
//                header('Content-Type: application/json', true, $code);
//            }
//            echo json_encode($data);
            die();
        }

        public static function respondSuccess($message)
        {
            return self::respond([
                'object' => 'message',
                'status' => 'success',
                'data' => [
                    'message' => $message
                ]
            ], 200);
        }

        public static function respondError($message, $code = 400)
        {

            return self::respond([
                'object' => 'error',
                'status' => 'error',
                'data' => [
                    'message' => $message,
                    'code' => $code
                ]
            ], $code);
        }


        public function respondObject($data, $name)
        {
            return self::respond([
                'object' => $name,
                'status' => 'success',
                'data' => $data
            ], 200);
        }


        public static function inputOrDefault($field, $key)
        {
            return (isset(self::$inputJson[$field]) ? self::$inputJson[$field] : config('constants.' . $key)[$field]);
        }
    }