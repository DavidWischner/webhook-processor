<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientBridge;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\WebhookMessageDispatcher\ZedGatewayDispatcherPlugin;

class WebhookProcessorDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const string CLIENT_ZED_REQUEST = 'CLIENT_ZED_REQUEST';

    /**
     * @var string
     */
    public const string PLUGIN_WEBHOOK_MESSAGE_DISPATCHER = 'PLUGIN_WEBHOOK_MESSAGE_DISPATCHER';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addZedRequestClient($container);
        $container = $this->addWebhookMessageDispatcherPlugin($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addZedRequestClient(Container $container): Container
    {
        $container->set(static::CLIENT_ZED_REQUEST, function (Container $container) {
            return new WebhookProcessorToZedRequestClientBridge(
                $container->getLocator()->zedRequest()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addWebhookMessageDispatcherPlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_WEBHOOK_MESSAGE_DISPATCHER, function (): WebhookMessageDispatcherPluginInterface {
            return $this->getWebhookMessageDispatcherPlugin();
        });

        return $container;
    }

    /**
     * Overwrite this method in a project to dispatch webhook messages differently
     * (e.g. to a buffer/queue) instead of forwarding them to the Zed gateway.
     *
     * @return \SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface
     */
    protected function getWebhookMessageDispatcherPlugin(): WebhookMessageDispatcherPluginInterface
    {
        return new ZedGatewayDispatcherPlugin();
    }
}
