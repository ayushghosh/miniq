<?php

    if(db()->getSchemaBuilder()->hasTable('jobs'))
    {
        echo "Table jobs exist".PHP_EOL;
        return;
    }
    db()->getSchemaBuilder()->create('jobs', function ($table) {

        $table->bigIncrements('id');
        $table->string('queue');
        $table->longText('payload');
        $table->tinyInteger('attempts')->unsigned();
        $table->boolean('reserved')->default(0);
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at');
        $table->unsignedInteger('created_at');
        $table->index(['queue', 'reserved_at']);
    });