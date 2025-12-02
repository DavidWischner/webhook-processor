<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\RestWebhookProcessorResponseAttributesTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig;
use Symfony\Component\HttpFoundation\Response;

class WebhookProcessorRestResponseBuilder implements WebhookProcessorRestResponseBuilderInterface
{
    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig $config
     */
    public function __construct(
        protected RestResourceBuilderInterface $restResourceBuilder,
        protected WebhookProcessorConfig $config,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createRestResponse(): RestResponseInterface
    {
        return $this->restResourceBuilder->createRestResponse();
    }

    /**
     * {@inheritDoc}
     *
     * @param \Generated\Shared\Transfer\WebhookProcessorResponseTransfer $processorResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createSuccessResponse(WebhookProcessorResponseTransfer $processorResponseTransfer): RestResponseInterface
    {
        $responseAttributesTransfer = (new RestWebhookProcessorResponseAttributesTransfer())
            ->setSuccess(true)
            ->setMessage($processorResponseTransfer->getMessage())
            ->setProcessedBy($processorResponseTransfer->getProcessedBy());

        $restResource = $this->restResourceBuilder->createRestResource(
            $this->config->getResourceType(),
            null,
            $responseAttributesTransfer,
        );

        return $this->restResourceBuilder->createRestResponse()->addResource($restResource);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $message
     * @param int $httpStatusCode
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createErrorResponse(string $message, int $httpStatusCode = Response::HTTP_BAD_REQUEST): RestResponseInterface
    {
        $errorTransfer = (new RestErrorMessageTransfer())
            ->setCode((string)$httpStatusCode)
            ->setStatus($httpStatusCode)
            ->setDetail($message);

        return $this->restResourceBuilder->createRestResponse()->addError($errorTransfer);
    }
}
