<?php

    /**
     * Created by PhpStorm.
     * User: ayush
     * Date: 30/10/16
     * Time: 2:11 AM
     */
    class QueueException extends Exception
    {
        public function errorMessage()
        {
            return respondError(['message'=> $this->getMessage()], $this->getCode());
        }

        public function __toString()
        {
//            respondError($this->getMessage(), $this->getCode());
        }
    }