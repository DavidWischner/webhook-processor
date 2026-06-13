<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Buffer;

use Generated\Shared\Transfer\WebhookMessageTransfer;

interface WebhookRedisBufferInterface
{
    /**
     * Specification:
     * - Serializes the webhook message and pushes it onto the configured Redis inbox list.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return void
     */
    public function push(WebhookMessageTransfer $webhookMessageTransfer): void;
}
