<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Processor;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;

class WebhookProcessor implements WebhookProcessorInterface
{
/**
     * @param array<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookProcessorPluginInterface> $processorPlugins
     * @param array<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookPreProcessorPluginInterface> $preProcessorPlugins
     */
    public function __construct(
        protected array $processorPlugins,
        protected array $preProcessorPlugins,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    public function processWebhook(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        $webhookMessageTransfer = $this->applyPreProcessors($webhookMessageTransfer);

        foreach ($this->processorPlugins as $processorPlugin) {
            if ($processorPlugin->isApplicable($webhookMessageTransfer)) {
                return $processorPlugin->process($webhookMessageTransfer);
            }
        }

        return $this->createNoProcessorFoundResponse();
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookMessageTransfer
     */
    protected function applyPreProcessors(WebhookMessageTransfer $webhookMessageTransfer): WebhookMessageTransfer
    {
        foreach ($this->preProcessorPlugins as $preProcessorPlugin) {
            if ($preProcessorPlugin->isApplicable($webhookMessageTransfer)) {
                $webhookMessageTransfer = $preProcessorPlugin->process($webhookMessageTransfer);
            }
        }

        return $webhookMessageTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    protected function createNoProcessorFoundResponse(): WebhookProcessorResponseTransfer
    {
        return (new WebhookProcessorResponseTransfer())
            ->setSuccess(false)
            ->setMessage('No applicable processor found for the webhook message');
    }
}
