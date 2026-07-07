<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\EventSubscriber;

use Spryker\Shared\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Logs incoming webhook requests (IP, headers, raw body) at INFO level when
 * the environment variable WEBHOOK_REQUEST_LOGGING_ENABLED=true is set.
 * Runs at priority 1024, before any request transformers.
 */
class WebhookProcessorRequestLoggerSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const string RESOURCE_PATH_SEGMENT = 'webhook-processor';

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1024],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!filter_var(getenv('WEBHOOK_REQUEST_LOGGING_ENABLED'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $request = $event->getRequest();

        if (!str_contains($request->getPathInfo(), static::RESOURCE_PATH_SEGMENT)) {
            return;
        }

        if ($request->getMethod() !== 'POST') {
            return;
        }

        $this->getLogger()->info('Webhook request received.', [
            'ip' => $request->getClientIp(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);
    }
}
