<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\WebhookMessageDispatcher;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Shared\Log\LoggerTrait;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorFactory getFactory()
 */
class RedisInboxDispatcherPlugin extends AbstractPlugin implements WebhookMessageDispatcherPluginInterface
{
    use LoggerTrait;

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\WebhookMessageTransfer $webhookMessageTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function dispatch(WebhookMessageTransfer $webhookMessageTransfer): RestResponseInterface
    {
        $restResponseBuilder = $this->getFactory()->createRestResponseBuilder();

        try {
            $this->getFactory()->createWebhookRedisBuffer()->push($webhookMessageTransfer);
        } catch (\Throwable $throwable) {
            $this->getLogger()->error('Failed to push webhook message to the Redis inbox.', [
                'exception' => $throwable->getMessage(),
                'type' => $webhookMessageTransfer->getType(),
            ]);

            return $restResponseBuilder->createErrorResponse(
                'Failed to queue webhook message',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return $restResponseBuilder->createSuccessResponse(
            (new WebhookProcessorResponseTransfer())
                ->setSuccess(true)
                ->setMessage('Webhook accepted')
                ->setProcessedBy(static::class),
        );
    }
}
