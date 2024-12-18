<?php

namespace App\Infrastructure\Queue\RabbitMQ;

use App\Domain\Common\Jobs\Contracts\Job;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Queue\Contracts\QueueManagerInterface;
use DI\Container;
use RuntimeException;
use Src\Domain\Common\Queue\Exceptions\InvalidJobException;
use Throwable;

class QueueManager implements QueueManagerInterface
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct(
        int $maxRetries,
        int $retryDelaySeconds,
        private LoggerInterface $logger,
        private Container $container,
    ) {
        $this->connect($maxRetries, $retryDelaySeconds);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @throws Throwable
     */
    public function publish(mixed $message, string $queue): void
    {
        $this->declareQueue($queue);

        try {
            $serialized = new AMQPMessage(serialize($message));
            $this->channel->basic_publish($serialized, routing_key: $queue);
        } catch (Throwable $exception) {
            $this->logger->critical(sprintf('[%s] Failed to publish message.', __METHOD__), [
                'ex' => (string) $exception,
                'queue' => $queue,
            ]);

            throw $exception;
        }

        $this->logger->info(sprintf('[%s] Message published successfully.', __METHOD__), [
            'queue' => $queue,
        ]);
    }

    public function consume(string $queue): void
    {
        $this->declareQueue($queue);

        try {
            $this->channel->basic_consume(
                $queue,
                no_local: false,
                no_ack: false,
                exclusive: false,
                nowait: false,
                callback: fn ($message) => $this->callback($message),
            );

            $this->channel->consume();

            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
        } catch (Throwable $exception) {
            $this->logger->error(sprintf('[%s] Failed to consume message.', __METHOD__), [
                'ex' => (string) $exception,
                'queue' => $queue,
            ]);
        }
    }

    /**
     * @throws Throwable
     */
    private function declareQueue(string $queue): void
    {
        try {
            $this->channel->queue_declare(
                $queue,
                passive: false,
                durable: true,
                exclusive: false,
                auto_delete: false,
                nowait: false,
                arguments: [],
                ticket: null,
            );
        } catch (Throwable $exception) {
            $this->logger->critical(sprintf('[%s] Failed to declare queue.', __METHOD__), [
                'ex' => (string) $exception,
                'queue' => $queue,
            ]);

            throw $exception;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function callback(AMQPMessage $message): void
    {
        /** @var Job|mixed $job */
        $job = unserialize($message->getBody());

        if (! $job instanceof Job) {
            throw new InvalidJobException();
        }

        $this->container->injectOn($job);

        $jobClass = get_class($job);

        $this->logger->info(sprintf('[%s] Processing message.' . PHP_EOL, $jobClass));

        try {
            $job->handle();

            $message->ack();

            $this->logger->info(sprintf('[%s] Message processed.' . PHP_EOL, $jobClass));
        } catch (Throwable $exception) {
            $job->incrementAttempts();

            if ($job->shouldRetry()) {
                $message->nack(requeue: false);

                $this->publish($job, $job->getQueue());

                $this->logger->error(sprintf('[%s] Failed to process message. Retrying...', $jobClass), [
                    'ex' => (string) $exception,
                    'attempts' => $job->getAttempts(),
                ]);

                return;
            }

            $message->nack(requeue: false);

            $this->logger->error(sprintf('[%s] Failed to process message.' . PHP_EOL, $jobClass), [
                'ex' => (string) $exception,
                'attempts' => $job->getAttempts(),
            ]);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function connect(int $maxRetries, int $retryDelaySeconds): void
    {
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $this->connection = new AMQPStreamConnection(
                    config('queue.rabbitmq.host'),
                    config('queue.rabbitmq.port'),
                    config('queue.rabbitmq.user'),
                    config('queue.rabbitmq.password')
                );
                $this->channel = $this->connection->channel();

                break;
            } catch (Throwable $ex) {
                $this->logger->critical(sprintf('[%s] Failed to connect to RabbitMQ', __METHOD__), [
                    'ex' => (string) $ex,
                    'attempt' => $attempt,
                ]);

                sleep($retryDelaySeconds);
            }
        }

        if (!isset($this->connection) || !isset($this->channel)) {
            throw new RuntimeException('Failed to connect to RabbitMQ.');
        }
    }

    public function disconnect(): void
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
}
