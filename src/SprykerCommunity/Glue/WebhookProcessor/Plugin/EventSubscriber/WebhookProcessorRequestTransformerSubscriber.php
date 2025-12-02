<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Transforms CloudEvents format to JSON-API format for webhook-processor endpoint
 */
class WebhookProcessorRequestTransformerSubscriber implements EventSubscriberInterface
{
    protected const string RESOURCE_TYPE = 'webhook-processor';

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 512],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only process webhook-processor endpoint
        if (!str_contains($request->getPathInfo(), static::RESOURCE_TYPE)) {
            return;
        }

        // Only process POST requests
        if ($request->getMethod() !== 'POST') {
            return;
        }

        $content = $request->getContent();
        if (!$content) {
            return;
        }

        $data = json_decode($content, true);
        if (!$data || !is_array($data)) {
            return;
        }

        // Check if it's CloudEvents format (has 'type' field at root level)
        // CloudEvents has: {"type": "...", "data": {...}, "id": "..."}
        // JSON-API has: {"data": {"type": "...", "attributes": {...}}}
        if (isset($data['type']) && !isset($data['data']['type'])) {
            $jsonApiData = [
                'data' => [
                    'type' => static::RESOURCE_TYPE,
                    'attributes' => $data,
                ],
            ];

            // Symfony's Request::$content is protected with no public setter.
            // Reflection is the only way to replace the body after object creation.
            // setAccessible() is intentionally omitted — deprecated since PHP 8.1, not needed.
            $contentProperty = (new \ReflectionClass($request))->getProperty('content');
            $contentProperty->setValue($request, json_encode($jsonApiData));
        }
    }
}
