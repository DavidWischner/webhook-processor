<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Controller;

use Generated\Shared\Transfer\GlueErrorTransfer;
use Generated\Shared\Transfer\GlueRequestTransfer;
use Generated\Shared\Transfer\GlueResponseTransfer;
use Generated\Shared\Transfer\WebhookMessageTransfer;
use Spryker\Glue\Kernel\Backend\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorFactory getFactory()
 */
class WebhookProcessorResourceController extends AbstractController
{
    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     *
     * @return \Generated\Shared\Transfer\GlueResponseTransfer
     */
    public function postAction(GlueRequestTransfer $glueRequestTransfer): GlueResponseTransfer
    {
        $content = $glueRequestTransfer->getContent();

        if (!$content) {
            return $this->buildErrorResponse(Response::HTTP_BAD_REQUEST, 'Empty request body');
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            return $this->buildErrorResponse(Response::HTTP_BAD_REQUEST, 'Invalid JSON body');
        }

        $webhookMessageTransfer = $this->mapToWebhookMessage($data);

        if (!$webhookMessageTransfer->getType()) {
            return $this->buildErrorResponse(Response::HTTP_BAD_REQUEST, 'Missing or empty message type');
        }

        $responseTransfer = $this->getFactory()
            ->getWebhookProcessorFacade()
            ->processWebhook($webhookMessageTransfer);

        if (!$responseTransfer->getSuccess()) {
            return $this->buildErrorResponse(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $responseTransfer->getMessage() ?? 'No applicable processor found for the webhook message',
            );
        }

        return (new GlueResponseTransfer())
            ->setHttpStatus(Response::HTTP_OK)
            ->setContent((string)json_encode([
                'data' => [
                    'type' => 'webhook-processor',
                    'id' => null,
                    'attributes' => [
                        'success' => true,
                        'message' => $responseTransfer->getMessage(),
                        'processedBy' => $responseTransfer->getProcessedBy(),
                    ],
                ],
            ]));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return \Generated\Shared\Transfer\WebhookMessageTransfer
     */
    protected function mapToWebhookMessage(array $data): WebhookMessageTransfer
    {
        // CloudEvents format (specversion is a required field in the spec)
        if (isset($data['specversion'])) {
            return (new WebhookMessageTransfer())
                ->setType((string)($data['type'] ?? ''))
                ->setPayload((array)($data['data'] ?? []))
                ->setMetadata(array_filter([
                    'id' => $data['id'] ?? null,
                    'source' => $data['source'] ?? null,
                    'specversion' => $data['specversion'],
                    'subject' => $data['subject'] ?? null,
                    'time' => $data['time'] ?? null,
                    'datacontenttype' => $data['datacontenttype'] ?? null,
                    'dataschema' => $data['dataschema'] ?? null,
                ]));
        }

        // JSON-API format
        $attributes = $data['data']['attributes'] ?? [];

        return (new WebhookMessageTransfer())
            ->setType((string)($attributes['type'] ?? ''))
            ->setPayload((array)($attributes['payload'] ?? []));
    }

    /**
     * @param int $status
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\GlueResponseTransfer
     */
    protected function buildErrorResponse(int $status, string $message): GlueResponseTransfer
    {
        $glueErrorTransfer = (new GlueErrorTransfer())
            ->setStatus($status)
            ->setMessage($message);

        return (new GlueResponseTransfer())
            ->setHttpStatus($status)
            ->addError($glueErrorTransfer);
    }
}
