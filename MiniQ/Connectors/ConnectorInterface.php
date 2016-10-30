<?php

    /**
     * Interface ConnectorInterface
     */
    interface ConnectorInterface
    {
        /**
         * @param array $config
         * @return mixed
         */
        public function connect(array $config);
    }