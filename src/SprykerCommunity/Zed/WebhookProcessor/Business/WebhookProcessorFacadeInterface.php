<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;

interface WebhookProcessorFacadeInterface
{
    /**
     * Specification:
     * - Processes a webhook message using the configured processor plugins.
     * - Iterates through the processor plugins until one is applicable.
     * - Returns a response indicating success or failure.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function processWebhook(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer;

    /**
     * Specification:
     * - Routes the message to the configured queue based on message type.
     * - Returns a response indicating success or failure.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function routeToQueue(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer;

    /**
     * Specification:
     * - Sends a webhook message to a specified queue.
     * - Uses the configured queue client and pool name.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     * @param string $queueName
     *
     * @return void
     */
    public function sendToQueue(WebhookMessageTransfer $webhookMessageTransfer, string $queueName): void;
}
