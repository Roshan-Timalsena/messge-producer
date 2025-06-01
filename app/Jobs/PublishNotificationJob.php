<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PublishNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password')
        );

        $channel = $connection->channel();
        $channel->queue_declare('notifications', false, true, false, false);

        $msg = new AMQPMessage(json_encode([
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'message' => $this->notification->message
        ]));

        $channel->basic_publish($msg, '', 'notifications');
        $channel->close();
        $connection->close();
    }
}
