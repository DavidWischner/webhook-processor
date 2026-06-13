<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Dependency\Redis;

interface WebhookProcessorToRedisInterface
{
    /**
     * @param string $listKey
     * @param string $value
     *
     * @return void
     */
    public function push(string $listKey, string $value): void;
}
