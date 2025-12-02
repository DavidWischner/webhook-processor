<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorBusinessFactory getFactory()
 */
class WebhookProcessorFacade extends AbstractFacade implements WebhookProcessorFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function processWebhook(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        return $this->getFactory()
            ->createWebhookProcessor()
            ->processWebhook($webhookMessageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function routeToQueue(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        return $this->getFactory()
            ->createQueueRouter()
            ->routeToQueue($webhookMessageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     * @param string $queueName
     *
     * @return void
     */
    public function sendToQueue(WebhookMessageTransfer $webhookMessageTransfer, string $queueName): void
    {
        $this->getFactory()
            ->createQueueSender()
            ->sendToQueue($webhookMessageTransfer, $queueName);
    }
}
