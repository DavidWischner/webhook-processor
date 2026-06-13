<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Buffer;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Redis\WebhookProcessorToRedisInterface;

class WebhookRedisBuffer implements WebhookRedisBufferInterface
{
    /**
     * @param \SprykerCommunity\Glue\WebhookProcessor\Dependency\Redis\WebhookProcessorToRedisInterface $redisClient
     * @param string $listKey
     */
    public function __construct(
        protected WebhookProcessorToRedisInterface $redisClient,
        protected string $listKey,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return void
     */
    public function push(WebhookMessageTransfer $webhookMessageTransfer): void
    {
        $this->redisClient->push(
            $this->listKey,
            (string)json_encode($webhookMessageTransfer->toArray()),
        );
    }
}
