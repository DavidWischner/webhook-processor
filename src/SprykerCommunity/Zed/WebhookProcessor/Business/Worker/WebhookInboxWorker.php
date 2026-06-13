<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Worker;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerCommunity\Zed\WebhookProcessor\Business\Processor\WebhookProcessorInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Redis\WebhookProcessorToRedisInterface;

class WebhookInboxWorker implements WebhookInboxWorkerInterface
{
    use LoggerTrait;

    /**
     * How long a single pop blocks waiting for a message before returning empty, so the
     * deadline below is still checked regularly instead of blocking indefinitely.
     *
     * @var int
     */
    protected const int POP_BLOCKING_TIMEOUT_SECONDS = 1;

    /**
     * Backoff after a lost Redis connection, so a Redis outage doesn't turn into a tight
     * reconnect-fail loop for the rest of the run.
     *
     * @var int
     */
    protected const int RECONNECT_BACKOFF_MICROSECONDS = 1_000_000;

    /**
     * @param \SprykerCommunity\Zed\WebhookProcessor\Dependency\Redis\WebhookProcessorToRedisInterface $redisClient
     * @param \SprykerCommunity\Zed\WebhookProcessor\Business\Processor\WebhookProcessorInterface $webhookProcessor
     * @param string $listKey
     */
    public function __construct(
        protected WebhookProcessorToRedisInterface $redisClient,
        protected WebhookProcessorInterface $webhookProcessor,
        protected string $listKey,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $timeLimit
     *
     * @return void
     */
    public function run(int $timeLimit): void
    {
        $deadline = time() + $timeLimit;

        while (time() < $deadline) {
            try {
                $json = $this->redisClient->pop($this->listKey, static::POP_BLOCKING_TIMEOUT_SECONDS);
            } catch (\RedisException $redisException) {
                $this->getLogger()->error('Lost connection to the Redis inbox while waiting for messages; reconnecting.', [
                    'exception' => $redisException->getMessage(),
                ]);

                usleep(static::RECONNECT_BACKOFF_MICROSECONDS);

                continue;
            }

            if ($json === null) {
                continue;
            }

            $this->processMessage($json);
        }
    }

    /**
     * @param string $json
     *
     * @return void
     */
    protected function processMessage(string $json): void
    {
        try {
            $data = json_decode($json, true);

            if (!is_array($data)) {
                $this->getLogger()->error('Discarded buffered webhook message: payload is not valid JSON.', [
                    'payload' => $json,
                ]);

                return;
            }

            $webhookMessageTransfer = (new WebhookMessageTransfer())->fromArray($data, true);
            $responseTransfer = $this->webhookProcessor->processWebhook($webhookMessageTransfer);

            if (!$responseTransfer->getSuccess()) {
                $this->getLogger()->error('Failed to process buffered webhook message.', [
                    'reason' => $responseTransfer->getMessage(),
                    'payload' => $json,
                ]);
            }
        } catch (\Throwable $throwable) {
            // Caught so a single malformed/unroutable message can't crash the worker loop and block the rest of the inbox until the next cron run.
            $this->getLogger()->error('Discarded buffered webhook message after an unexpected error.', [
                'exception' => $throwable->getMessage(),
                'payload' => $json,
            ]);
        }
    }
}
