<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Router;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;

interface QueueRouterInterface
{
    /**
     * Specification:
     * - Returns true if the message type has a configured queue mapping.
     *
     * @api
     *
     * @param string $messageType
     *
     * @return bool
     */
    public function isMessageTypeMapped(string $messageType): bool;

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
}
