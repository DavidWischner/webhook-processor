<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\WebhookMessageDispatcher;

use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorGatewayRequestTransfer;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorFactory getFactory()
 */
class ZedGatewayDispatcherPlugin extends AbstractPlugin implements WebhookMessageDispatcherPluginInterface
{
    /**
     * @var string
     */
    protected const string GATEWAY_URL = '/webhook-processor/gateway/process-webhook';

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

        $gatewayRequestTransfer = (new WebhookProcessorGatewayRequestTransfer())
            ->setWebhookMessage($webhookMessageTransfer);

        $requestOptions = [];
        $timeout = $this->getFactory()->getConfig()->getZedRequestTimeout();
        if ($timeout > 0) {
            $requestOptions['timeout'] = $timeout;
        }

        try {
            /** @var \Generated\Shared\Transfer\WebhookProcessorGatewayResponseTransfer $gatewayResponseTransfer */
            $gatewayResponseTransfer = $this->getFactory()->getZedRequestClient()->call(
                static::GATEWAY_URL,
                $gatewayRequestTransfer,
                $requestOptions ?: null,
            );
        } catch (GuzzleException $e) {
            if ($this->isTimeoutException($e)) {
                return $restResponseBuilder->createErrorResponse(
                    'Gateway timeout',
                    Response::HTTP_TOO_MANY_REQUESTS,
                );
            }

            return $restResponseBuilder->createErrorResponse(
                'Gateway error',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $processorResponseTransfer = $gatewayResponseTransfer->getWebhookProcessorResponse();

        if (!$gatewayResponseTransfer->getIsSuccess()) {
            return $restResponseBuilder->createErrorResponse(
                $processorResponseTransfer->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $restResponseBuilder->createSuccessResponse($processorResponseTransfer);
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
        return str_contains($e->getMessage(), 'cURL error 28');
    }
}
