<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Mapper;

use Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer;
use Generated\Shared\Transfer\WebhookMessageTransfer;

class WebhookMessageMapper implements WebhookMessageMapperInterface
{
    /**
     * {@inheritDoc}
     *
     * @param \Generated\Shared\Transfer\RestWebhookProcessorRequestAttributesTransfer $requestAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\WebhookMessageTransfer
     */
    public function mapAttributesToWebhookMessage(
        RestWebhookProcessorRequestAttributesTransfer $requestAttributesTransfer,
    ): WebhookMessageTransfer {
        return (new WebhookMessageTransfer())
            ->setType($requestAttributesTransfer->getType())
            ->setPayload($requestAttributesTransfer->getPayload())
            ->setMetadata($requestAttributesTransfer->getMetadata());
    }
}
