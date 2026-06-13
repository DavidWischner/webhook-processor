<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Processor;

use Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface;
use SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface;
use SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface;

class WebhookProcessor implements WebhookProcessorInterface
{
    /**
     * @param \SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface $webhookMessageMapper
     * @param \SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface $requestValidator
     * @param \SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface $restResponseBuilder
     * @param \SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface $webhookMessageDispatcherPlugin
     */
    public function __construct(
        protected WebhookMessageMapperInterface $webhookMessageMapper,
        protected WebhookProcessorRequestValidatorInterface $requestValidator,
        protected WebhookProcessorRestResponseBuilderInterface $restResponseBuilder,
        protected WebhookMessageDispatcherPluginInterface $webhookMessageDispatcherPlugin,
    ) {
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function processWebhook(RestRequestInterface $restRequest): RestResponseInterface
    {
        $requestAttributesTransfer = $restRequest->getResource()->getAttributes();

        if (!$requestAttributesTransfer instanceof RestWebhookProcessorRequestAttributesTransfer) {
            return $this->restResponseBuilder->createErrorResponse('Invalid request attributes');
        }

        $restResponse = $this->restResponseBuilder->createRestResponse();

        if (!$this->requestValidator->validate($requestAttributesTransfer, $restResponse)) {
            return $restResponse;
        }

        $webhookMessageTransfer = $this->webhookMessageMapper->mapAttributesToWebhookMessage($requestAttributesTransfer);

        return $this->webhookMessageDispatcherPlugin->dispatch($webhookMessageTransfer);
    }
}
