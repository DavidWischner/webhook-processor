<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\EventDispatcher;

use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\EventSubscriber\WebhookProcessorRequestLoggerSubscriber;

/**
 * Registers the WebhookProcessorRequestLoggerSubscriber with the GlueBackend event dispatcher.
 * Add to EventDispatcherDependencyProvider::getBackendEventDispatcherPlugins().
 */
class WebhookProcessorBackendEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addSubscriber(new WebhookProcessorRequestLoggerSubscriber());

        return $eventDispatcher;
    }
}
