<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\Backend\AbstractBackendApiFactory;
use SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface;

class WebhookProcessorFactory extends AbstractBackendApiFactory
{
    /**
     * @return \SprykerCommunity\Zed\WebhookProcessor\Business\WebhookProcessorFacadeInterface
     */
    public function getWebhookProcessorFacade(): WebhookProcessorFacadeInterface
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::FACADE_WEBHOOK_PROCESSOR);
    }
}
