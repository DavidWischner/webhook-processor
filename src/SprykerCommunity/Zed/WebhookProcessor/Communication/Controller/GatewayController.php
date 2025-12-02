<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Communication\Controller;

use Generated\Shared\Transfer\WebhookProcessorGatewayRequestTransfer;
use Generated\Shared\Transfer\WebhookProcessorGatewayResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface getFacade()
 */
class GatewayController extends AbstractGatewayController
{
    /**
     * @param \Generated\Shared\Transfer\WebhookProcessorGatewayRequestTransfer $webhookProcessorGatewayRequestTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorGatewayResponseTransfer
     */
    public function processWebhookAction(
        WebhookProcessorGatewayRequestTransfer $webhookProcessorGatewayRequestTransfer
    ): WebhookProcessorGatewayResponseTransfer {
        $webhookMessageTransfer = $webhookProcessorGatewayRequestTransfer->getWebhookMessage();

        $webhookProcessorResponseTransfer = $this->getFacade()->processWebhook($webhookMessageTransfer);

        return (new WebhookProcessorGatewayResponseTransfer())
            ->setWebhookProcessorResponse($webhookProcessorResponseTransfer)
            ->setIsSuccess($webhookProcessorResponseTransfer->getSuccess());
    }
}
