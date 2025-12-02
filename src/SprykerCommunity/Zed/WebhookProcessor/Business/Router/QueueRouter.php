<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business\Router;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use SprykerCommunity\Zed\WebhookProcessor\Business\Sender\QueueSenderInterface;
use SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig;

class QueueRouter implements QueueRouterInterface
{
/**
     * @param \SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig $config
     * @param \SprykerCommunity\Zed\WebhookProcessor\Business\Sender\QueueSenderInterface $queueSender
     */
    public function __construct(
        protected WebhookProcessorConfig $config,
        protected QueueSenderInterface $queueSender,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $messageType
     *
     * @return bool
     */
    public function isMessageTypeMapped(string $messageType): bool
    {
        return isset($this->config->getMessageTypeToQueueMapping()[$messageType]);
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
        $messageType = $webhookMessageTransfer->getTypeOrFail();
        $queueName = $this->config->getMessageTypeToQueueMapping()[$messageType];

        try {
            $this->queueSender->sendToQueue($webhookMessageTransfer, $queueName);

            return $this->createSuccessResponse($queueName);
        } catch (\Throwable $throwable) {
            return $this->createErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @param string $queueName
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    protected function createSuccessResponse(string $queueName): WebhookProcessorResponseTransfer
    {
        return (new WebhookProcessorResponseTransfer())
            ->setSuccess(true)
            ->setMessage(sprintf('Message successfully routed to queue: %s', $queueName))
            ->setProcessedBy(static::class);
    }

    /**
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\WebhookProcessorResponseTransfer
     */
    protected function createErrorResponse(string $message): WebhookProcessorResponseTransfer
    {
        return (new WebhookProcessorResponseTransfer())
            ->setSuccess(false)
            ->setMessage($message);
    }
}
