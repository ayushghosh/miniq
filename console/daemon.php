<?php


    require_once '../bootstrap/app.php';


    $q = new Queue();

    while (true) {
        $q->releaseExpiredJobs();
        sleep(1);
    }

    