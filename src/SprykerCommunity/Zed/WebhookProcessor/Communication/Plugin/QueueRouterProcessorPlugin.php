<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Communication\Plugin;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookProcessorPluginInterface;

/**
 * @method \SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig getConfig()
 * @method \SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface getFacade()
 */
class QueueRouterProcessorPlugin extends AbstractPlugin implements WebhookProcessorPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return bool
     */
    public function isApplicable(WebhookMessageTransfer $webhookMessageTransfer): bool
    {
        $messageType = $webhookMessageTransfer->getType();

        return $messageType !== null && isset($this->getConfig()->getMessageTypeToQueueMapping()[$messageType]);
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
    public function process(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        return $this->getFacade()->routeToQueue($webhookMessageTransfer);
    }
}
