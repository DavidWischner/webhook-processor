<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Processor;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Shared\Log\LoggerTrait;

class WebhookProcessor implements WebhookProcessorInterface
{
    use LoggerTrait;
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

        return $this->createNoProcessorFoundResponse($webhookMessageTransfer);
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
    protected function createNoProcessorFoundResponse(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        $this->getLogger()->warning('No applicable processor found for webhook message type.', [
            'type' => $webhookMessageTransfer->getType(),
        ]);

        return (new WebhookProcessorResponseTransfer())
            ->setSuccess(false)
            ->setMessage('No applicable processor found for the webhook message');
    }
}
