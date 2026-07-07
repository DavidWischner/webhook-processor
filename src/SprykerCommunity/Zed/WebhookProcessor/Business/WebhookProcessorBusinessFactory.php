<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerCommunity\Zed\WebhookProcessor\Business\Processor\WebhookProcessor;
use SprykerCommunity\Zed\WebhookProcessor\Business\Processor\WebhookProcessorInterface;
use SprykerCommunity\Zed\WebhookProcessor\Business\Router\QueueRouter;
use SprykerCommunity\Zed\WebhookProcessor\Business\Router\QueueRouterInterface;
use SprykerCommunity\Zed\WebhookProcessor\Business\Sender\QueueSender;
use SprykerCommunity\Zed\WebhookProcessor\Business\Sender\QueueSenderInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToQueueClientInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToStoreClientInterface;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Service\WebhookProcessorToUtilEncodingServiceInterface;
use SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorDependencyProvider;

/**
 * @method \SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig getConfig()
 */
class WebhookProcessorBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Business\Processor\WebhookProcessorInterface
     */
    public function createWebhookProcessor(): WebhookProcessorInterface
    {
        return new WebhookProcessor(
            $this->getProcessorPlugins(),
            $this->getPreProcessorPlugins(),
        );
    }

    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Business\Router\QueueRouterInterface
     */
    public function createQueueRouter(): QueueRouterInterface
    {
        return new QueueRouter(
            $this->getConfig(),
            $this->createQueueSender(),
        );
    }

    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Business\Sender\QueueSenderInterface
     */
    public function createQueueSender(): QueueSenderInterface
    {
        return new QueueSender(
            $this->getQueueClient(),
            $this->getStoreClient(),
            $this->getConfig(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return array<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookProcessorPluginInterface>
     */
    public function getProcessorPlugins(): array
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::PLUGINS_PROCESSOR);
    }

    /**
     * @return array<\SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookPreProcessorPluginInterface>
     */
    public function getPreProcessorPlugins(): array
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::PLUGINS_PRE_PROCESSOR);
    }

    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToQueueClientInterface
     */
    public function getQueueClient(): WebhookProcessorToQueueClientInterface
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::CLIENT_QUEUE);
    }

    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Dependency\Client\WebhookProcessorToStoreClientInterface
     */
    public function getStoreClient(): WebhookProcessorToStoreClientInterface
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Dependency\Service\WebhookProcessorToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): WebhookProcessorToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
