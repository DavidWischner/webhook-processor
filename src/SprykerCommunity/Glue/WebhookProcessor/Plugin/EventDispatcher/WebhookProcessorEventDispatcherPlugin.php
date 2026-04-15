<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\EventDispatcher;

use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\EventSubscriber\WebhookProcessorRequestLoggerSubscriber;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\EventSubscriber\WebhookProcessorRequestTransformerSubscriber;

/**
 * Registers the WebhookProcessor request transformer and logger subscribers
 */
class WebhookProcessorEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addSubscriber(new WebhookProcessorRequestLoggerSubscriber());
        $eventDispatcher->addSubscriber(new WebhookProcessorRequestTransformerSubscriber());

        return $eventDispatcher;
    }
}
