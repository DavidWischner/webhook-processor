<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface WebhookMessageDispatcherPluginInterface
{
    /**
     * Specification:
     * - Dispatches a validated and mapped webhook message to its destination (e.g. Zed gateway, queue, buffer).
     * - Returns the REST response that is sent back to the webhook caller.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function dispatch(WebhookMessageTransfer $webhookMessageTransfer): RestResponseInterface;
}
