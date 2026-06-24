<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\Backend\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Backend\Container;
use SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface;

class WebhookProcessorDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string FACADE_WEBHOOK_PROCESSOR = 'FACADE_WEBHOOK_PROCESSOR';

    /**
     * @param \Spryker\Glue\Kernel\Backend\Container $container
     *
     * @return \Spryker\Glue\Kernel\Backend\Container
     */
    public function provideBackendDependencies(Container $container): Container
    {
        $container->set(static::FACADE_WEBHOOK_PROCESSOR, static function (Container $container): WebhookProcessorFacadeInterface {
            return $container->getLocator()->webhookProcessor()->facade();
        });

        return $container;
    }
}
