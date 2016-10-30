<?php


    if(db()->getSchemaBuilder()->hasTable('failed_jobs'))
    {
        echo "Table failed_jobs exist".PHP_EOL;
        return;
    }
    db()->getSchemaBuilder()->create('failed_jobs', function ($table) {

        $table->increments('id');
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();

    });