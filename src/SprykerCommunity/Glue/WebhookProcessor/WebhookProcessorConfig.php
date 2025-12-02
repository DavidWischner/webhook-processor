<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class WebhookProcessorConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    protected const string RESOURCE_TYPE = 'webhook-processor';

    /**
     * @var string
     */
    protected const string CONTROLLER_NAME = 'webhook-processor-resource';

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return static::RESOURCE_TYPE;
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return static::CONTROLLER_NAME;
    }
}
