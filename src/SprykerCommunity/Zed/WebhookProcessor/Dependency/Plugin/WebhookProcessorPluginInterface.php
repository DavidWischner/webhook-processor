<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;

interface WebhookProcessorPluginInterface
{
    /**
     * Specification:
     * - Determines if this plugin can process the given webhook message.
     * - Should check the message type, payload structure, or other criteria.
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
     * - Processes the webhook message.
     * - Returns a response indicating success or failure.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function process(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer;
}
