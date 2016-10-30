# MiniQ

##Install

clone

composer install

copy example.env.php to env.php

update env.php

visit /install


## DEV DOC

http://miniq.ayush.me/apidoc/index.html

Deamon to update and remove expired jobs

console/daemon.php


API DOC

End Point

http://miniq.ayush.me

### Add Queue

POST /queues

Params

{
	"name":"q_welcome_mail",  (required)
	"visibility_timeout":90,
	"message_expiration" :5000,
	"maximum_message_size": 20000,
	"delay_seconds":100,
	"receive_message_wait_time_seconds": 0,
	"retries": 5,
	"retries_delay": 60
}



### List Queues

GET /queues


### Add Job to Queue

POST /queues/{queue name}/jobs

Params

{
	"payload":"message data here",
	"delay_seconds":12,
	"retries":10
}


### Receive Job from Queue

GET /queues/{queue name}/jobs/receive

### Update Timeout for Job

POST /queues/{queue name}/jobs/{job id}/timeout

Params

{
	"visibility_timeout":100
}


### Delete a Job

DELETE /queues/{queue name}/jobs/{job id}

Params






