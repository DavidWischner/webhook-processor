<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Processor;

use Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer;
use Generated\Shared\Transfer\WebhookProcessorGatewayRequestTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientInterface;
use SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface;
use SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface;
use SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface;

class WebhookProcessor implements WebhookProcessorInterface
{
    /**
     * @var string
     */
    protected const string GATEWAY_URL = '/webhook-processor/gateway/process-webhook';

    /**
     * @param \SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface $webhookMessageMapper
     * @param \SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface $requestValidator
     * @param \SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface $restResponseBuilder
     * @param \SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientInterface $zedRequestClient
     */
    public function __construct(
        protected WebhookMessageMapperInterface $webhookMessageMapper,
        protected WebhookProcessorRequestValidatorInterface $requestValidator,
        protected WebhookProcessorRestResponseBuilderInterface $restResponseBuilder,
        protected WebhookProcessorToZedRequestClientInterface $zedRequestClient,
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

        $gatewayRequestTransfer = (new WebhookProcessorGatewayRequestTransfer())
            ->setWebhookMessage($webhookMessageTransfer);

        /** @var \Generated\Shared\Transfer\WebhookProcessorGatewayResponseTransfer $gatewayResponseTransfer */
        $gatewayResponseTransfer = $this->zedRequestClient->call(
            static::GATEWAY_URL,
            $gatewayRequestTransfer,
        );

        $processorResponseTransfer = $gatewayResponseTransfer->getWebhookProcessorResponse();

        if (!$gatewayResponseTransfer->getIsSuccess()) {
            return $this->restResponseBuilder->createErrorResponse(
                $processorResponseTransfer->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->restResponseBuilder->createSuccessResponse($processorResponseTransfer);
    }
}
