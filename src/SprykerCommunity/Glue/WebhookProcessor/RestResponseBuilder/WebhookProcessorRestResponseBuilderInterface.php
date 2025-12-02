<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder;

use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

interface WebhookProcessorRestResponseBuilderInterface
{
    /**
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createRestResponse(): RestResponseInterface;

    /**
     * @param \Generated\Shared\Transfer\WebhookProcessorResponseTransfer $processorResponseTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createSuccessResponse(WebhookProcessorResponseTransfer $processorResponseTransfer): RestResponseInterface;

    /**
     * @param string $message
     * @param int $httpStatusCode
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createErrorResponse(string $message, int $httpStatusCode = Response::HTTP_BAD_REQUEST): RestResponseInterface;
}
