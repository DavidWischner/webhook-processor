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
        // CloudEvents requests carry the event payload in `data`; JSON-API requests use `payload`.
        $payload = $requestAttributesTransfer->getData() ?: $requestAttributesTransfer->getPayload();

        // CloudEvents requests have no `metadata` array — their envelope fields are mapped
        // to individual transfer properties instead. Collect them explicitly.
        $metadata = $requestAttributesTransfer->getMetadata();
        if (!$metadata) {
            $metadata = array_filter([
                'id' => $requestAttributesTransfer->getId(),
                'source' => $requestAttributesTransfer->getSource(),
                'specversion' => $requestAttributesTransfer->getSpecversion(),
                'subject' => $requestAttributesTransfer->getSubject(),
                'time' => $requestAttributesTransfer->getTime(),
                'datacontenttype' => $requestAttributesTransfer->getDatacontenttype(),
                'dataschema' => $requestAttributesTransfer->getDataschema(),
            ]);
        }

        return (new WebhookMessageTransfer())
            ->setType($requestAttributesTransfer->getType())
            ->setPayload($payload)
            ->setMetadata($metadata);
    }
}
