<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Validator;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class WebhookProcessorRequestValidator implements WebhookProcessorRequestValidatorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param \Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer $requestAttributesTransfer
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface $restResponse
     *
     * @return bool
     */
    public function validate(
        RestWebhookProcessorRequestAttributesTransfer $requestAttributesTransfer,
        RestResponseInterface $restResponse,
    ): bool {
        if (!$requestAttributesTransfer->getType()) {
            $restResponse->addError(
                (new RestErrorMessageTransfer())
                    ->setCode((string)Response::HTTP_BAD_REQUEST)
                    ->setStatus(Response::HTTP_BAD_REQUEST)
                    ->setDetail('Missing required field: type'),
            );

            return false;
        }

        return true;
    }
}
