<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerCommunity\Zed\WebhookProcessor\Communication\Plugin\QueueRouterProcessorPlugin;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToQueueClientBridge;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToStoreClientBridge;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Service\WebhookProcessorToUtilEncodingServiceBridge;

class WebhookProcessorDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const string CLIENT_QUEUE = 'CLIENT_QUEUE';

    /**
     * @var string
     */
    public const string CLIENT_STORE = 'CLIENT_STORE';

    /**
     * @var string
     */
    public const string SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const string PLUGINS_PROCESSOR = 'PLUGINS_PROCESSOR';

    /**
     * @var string
     */
    public const string PLUGINS_PRE_PROCESSOR = 'PLUGINS_PRE_PROCESSOR';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addProcessorPlugins($container);
        $container = $this->addPreProcessorPlugins($container);
        $container = $this->addQueueClient($container);
        $container = $this->addStoreClient($container);
        $container = $this->addUtilEncodingService($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addQueueClient(Container $container): Container
    {
        $container->set(static::CLIENT_QUEUE, static function (Container $container): WebhookProcessorToQueueClientBridge {
            return new WebhookProcessorToQueueClientBridge(
                $container->getLocator()->queue()->client()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProcessorPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PROCESSOR, function (): array {
            return $this->getProcessorPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, static function (Container $container): WebhookProcessorToStoreClientBridge {
            return new WebhookProcessorToStoreClientBridge(
                $container->getLocator()->store()->client()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, static function (Container $container): WebhookProcessorToUtilEncodingServiceBridge {
            return new WebhookProcessorToUtilEncodingServiceBridge(
                $container->getLocator()->utilEncoding()->service()
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPreProcessorPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRE_PROCESSOR, function (): array {
            return $this->getPreProcessorPlugins();
        });

        return $container;
    }

    /**
     * @return list<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookProcessorPluginInterface>
     */
    protected function getProcessorPlugins(): array
    {
        return [
            new QueueRouterProcessorPlugin(),
        ];
    }

    /**
     * @return list<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookPreProcessorPluginInterface>
     */
    protected function getPreProcessorPlugins(): array
    {
        return [];
    }
}
