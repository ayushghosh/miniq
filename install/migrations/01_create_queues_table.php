<?php

    if(db()->getSchemaBuilder()->hasTable('queues'))
    {
        echo "Table queues exist".PHP_EOL;
        return;
    }
    db()->getSchemaBuilder()->create('queues', function ($table) {
        
        $table->increments('id');
        $table->string('name')->unique();
        $table->integer('visibility_timeout')->default(60);
        $table->integer('message_expiration')->default(1209600);
        $table->integer('maximum_message_size')->default(262144);
        $table->integer('delay_seconds')->default(0);
        $table->integer('receive_message_wait_time_seconds')->default(0);
        $table->integer('retries')->default(1);
        $table->integer('retries_delay')->default(60);
        $table->integer('messages_available')->default(0);
        $table->integer('messages_in_flight')->default(0);
        $table->timestamps();
    });