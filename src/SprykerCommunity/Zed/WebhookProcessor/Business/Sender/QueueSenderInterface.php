<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Sender;

use Generated\Shared\Transfer\WebhookMessageTransfer;

interface QueueSenderInterface
{
    /**
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     * @param string $queueName
     *
     * @return void
     */
    public function sendToQueue(WebhookMessageTransfer $webhookMessageTransfer, string $queueName): void;
}
