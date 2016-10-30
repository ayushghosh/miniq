<?php


    class QueueException extends Exception
    {
        public function errorMessage()
        {
            return ApiController::respondError($this->getMessage(),$this->getCode());
        }

        public function __toString()
        {
        }
    }