<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Client;

use Generated\Shared\Transfer\QueueSendMessageTransfer;

interface WebhookProcessorToQueueClientInterface
{
    /**
     * @param string $queueName
     * @param \Generated\Shared\Transfer\QueueSendMessageTransfer $queueSendMessageTransfer
     *
     * @return void
     */
    public function sendMessage(string $queueName, QueueSendMessageTransfer $queueSendMessageTransfer): void;
}
