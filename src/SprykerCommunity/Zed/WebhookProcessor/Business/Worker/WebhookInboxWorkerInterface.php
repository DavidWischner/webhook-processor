<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Worker;

interface WebhookInboxWorkerInterface
{
    /**
     * Specification:
     * - Runs the Redis-inbox worker loop for the configured time limit.
     * - Blocks waiting for buffered webhook messages on the Redis inbox (no busy-polling).
     * - Runs each popped message through the same pre-processor + processor plugin stack as the synchronous gateway flow.
     *
     * @api
     *
     * @param int $timeLimit
     *
     * @return void
     */
    public function run(int $timeLimit): void;
}
