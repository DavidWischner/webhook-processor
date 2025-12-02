<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin;

use Generated\Shared\Transfer\WebhookMessageTransfer;

interface WebhookPreProcessorPluginInterface
{
    /**
     * Specification:
     * - Returns true if this pre-processor should be applied for the given webhook message.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return bool
     */
    public function isApplicable(WebhookMessageTransfer $webhookMessageTransfer): bool;

    /**
     * Specification:
     * - Pre-processes a webhook message before it is sent to the queue.
     * - Can modify the message, add metadata, validate, etc.
     * - Returns the modified message transfer.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookMessageTransfer
     */
    public function process(WebhookMessageTransfer $webhookMessageTransfer): WebhookMessageTransfer;
}
