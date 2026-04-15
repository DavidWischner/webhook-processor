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

class WebhookProcessorRequestLoggerSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    protected const string RESOURCE_TYPE = 'webhook-processor';

    /**
     * @return array
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

        if (!str_contains($request->getPathInfo(), static::RESOURCE_TYPE)) {
            return;
        }

        if ($request->getMethod() !== 'POST') {
            return;
        }

        $this->getLogger()->info('Webhook request received', [
            'ip' => $request->getClientIp(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);
    }
}
