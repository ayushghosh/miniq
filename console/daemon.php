<?php


    require_once '../bootstrap/app.php';


    $q = new MiniQ();

    while (true) {
        $q->daemonJobs();
        sleep(1);
    }

    