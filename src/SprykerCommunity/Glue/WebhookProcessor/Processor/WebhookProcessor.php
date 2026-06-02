<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Processor;

use Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer;
use Generated\Shared\Transfer\WebhookProcessorGatewayRequestTransfer;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientInterface;
use SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface;
use SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface;
use SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface;
use SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig;

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
     * @param \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig $config
     */
    public function __construct(
        protected WebhookMessageMapperInterface $webhookMessageMapper,
        protected WebhookProcessorRequestValidatorInterface $requestValidator,
        protected WebhookProcessorRestResponseBuilderInterface $restResponseBuilder,
        protected WebhookProcessorToZedRequestClientInterface $zedRequestClient,
        protected WebhookProcessorConfig $config,
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

        $requestOptions = [];
        $timeout = $this->config->getZedRequestTimeout();
        if ($timeout > 0) {
            $requestOptions['timeout'] = $timeout;
        }

        try {
            /** @var \Generated\Shared\Transfer\WebhookProcessorGatewayResponseTransfer $gatewayResponseTransfer */
            $gatewayResponseTransfer = $this->zedRequestClient->call(
                static::GATEWAY_URL,
                $gatewayRequestTransfer,
                $requestOptions ?: null,
            );
        } catch (GuzzleException $e) {
            if ($this->isTimeoutException($e)) {
                return $this->restResponseBuilder->createErrorResponse(
                    'Gateway timeout',
                    Response::HTTP_TOO_MANY_REQUESTS,
                );
            }

            return $this->restResponseBuilder->createErrorResponse(
                'Gateway error',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $processorResponseTransfer = $gatewayResponseTransfer->getWebhookProcessorResponse();

        if (!$gatewayResponseTransfer->getIsSuccess()) {
            return $this->restResponseBuilder->createErrorResponse(
                $processorResponseTransfer->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->restResponseBuilder->createSuccessResponse($processorResponseTransfer);
    }

    /**
     * @param \GuzzleHttp\Exception\GuzzleException $e
     *
     * @return bool
     */
    protected function isTimeoutException(GuzzleException $e): bool
    {
        if ($e instanceof GuzzleConnectException) {
            return true;
        }

        // cURL error 28 = CURLE_OPERATION_TIMEOUTED (read timeout via Guzzle's timeout option)
        return $e !== null && str_contains($e->getMessage(), 'cURL error 28');
    }
}
