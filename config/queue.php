<?php


    return [
        'default' => 'database',
        'drivers' => ['database'],
        'connections' => [
            'database' => [
                'driver' => 'database',
                'jobs_table' => 'jobs',
                'queue_table' => 'queues',
            ]
        ]
    ];