<?php


    if(db()->getSchemaBuilder()->hasTable('failed_jobs'))
    {
//        echo "Table failed_jobs exist".PHP_EOL;
        return;
    }
    db()->getSchemaBuilder()->create('failed_jobs', function ($table) {

        $table->bigInteger('id',true);
        $table->text('connection');
        $table->integer('queue_id');
        $table->foreign('queue_id')->references('id')->on('queues');
        $table->bigInteger('job_id');
//        $table->foreign('job_id')->references('id')->on('jobs');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();

    });