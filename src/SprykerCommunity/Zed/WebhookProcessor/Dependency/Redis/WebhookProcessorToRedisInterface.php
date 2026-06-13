<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Redis;

interface WebhookProcessorToRedisInterface
{
    /**
     * Blocks for up to `$blockingTimeoutSeconds` waiting for an element instead of polling.
     *
     * @param string $listKey
     * @param int $blockingTimeoutSeconds
     *
     * @throws \RedisException if the connection is lost while waiting.
     *
     * @return string|null
     */
    public function pop(string $listKey, int $blockingTimeoutSeconds): ?string;
}
