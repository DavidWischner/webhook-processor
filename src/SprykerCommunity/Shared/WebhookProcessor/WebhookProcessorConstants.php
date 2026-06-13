<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Shared\WebhookProcessor;

interface WebhookProcessorConstants
{
    /**
     * Timeout in seconds for the ZedRequest to the backend-gateway. 0 = no timeout.
     *
     * @var string
     */
    public const string WEBHOOK_ZED_TIMEOUT = 'WEBHOOK_PROCESSOR:WEBHOOK_ZED_TIMEOUT';

    /**
     * Redis list key used by the Redis-inbox dispatcher (Glue) and inbox worker (Zed) to buffer webhook messages.
     *
     * @var string
     */
    public const string WEBHOOK_REDIS_INBOX_KEY = 'WEBHOOK_PROCESSOR:WEBHOOK_REDIS_INBOX_KEY';
}
