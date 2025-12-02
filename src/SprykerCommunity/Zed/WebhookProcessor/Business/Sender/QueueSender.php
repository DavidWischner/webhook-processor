<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Sender;

use Generated\Shared\Transfer\QueueSendMessageTransfer;
use Generated\Shared\Transfer\WebhookMessageTransfer;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToQueueClientInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToStoreClientInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Service\WebhookProcessorToUtilEncodingServiceInterface;
use SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig;

class QueueSender implements QueueSenderInterface
{
    /**
     * @param \SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToQueueClientInterface $queueClient
     * @param \SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToStoreClientInterface $storeClient
     * @param \SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig $config
     * @param \SprykerCommunity\Zed\WebhookProcessor\Dependency\Service\WebhookProcessorToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        protected WebhookProcessorToQueueClientInterface $queueClient,
        protected WebhookProcessorToStoreClientInterface $storeClient,
        protected WebhookProcessorConfig $config,
        protected WebhookProcessorToUtilEncodingServiceInterface $utilEncodingService,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     * @param string $queueName
     *
     * @return void
     */
    public function sendToQueue(WebhookMessageTransfer $webhookMessageTransfer, string $queueName): void
    {
        $messageBody = $this->utilEncodingService->encodeJson($webhookMessageTransfer->toArray());

        $queueSendMessageTransfer = (new QueueSendMessageTransfer())
            ->setBody($messageBody)
            ->setQueueName($queueName);

        $poolName = $this->getPoolName();
        if ($poolName !== '') {
            $queueSendMessageTransfer->setQueuePoolName($poolName);
        }

        $this->queueClient->sendMessage($queueName, $queueSendMessageTransfer);
    }

    /**
     * @return string
     */
    protected function getPoolName(): string
    {
        $configuredPoolName = $this->config->getDefaultQueuePoolName();

        if ($configuredPoolName !== '') {
            return $configuredPoolName;
        }

        return $this->storeClient->getCurrentStore();
    }
}
