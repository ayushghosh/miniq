<?php

    if(db()->getSchemaBuilder()->hasTable('jobs'))
    {
//        echo "Table jobs exist".PHP_EOL;
        return;
    }
    db()->getSchemaBuilder()->create('jobs', function ($table) {

        $table->bigInteger('id',true);
//        $table->string('queue');
        $table->integer('queue_id');
        $table->foreign('queue_id')->references('id')->on('queues');
        $table->longText('payload');
        $table->tinyInteger('retries')->unsigned();
        $table->tinyInteger('max_retries')->unsigned();
        $table->boolean('reserved')->default(0);
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('expires_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
        $table->index(['queue_id', 'reserved_at']);
    });