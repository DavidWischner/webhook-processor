<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\AbstractBundleConfig;
use SprykerCommunity\Shared\WebhookProcessor\WebhookProcessorConfig as SharedWebhookProcessorConfig;

class WebhookProcessorConfig extends AbstractBundleConfig
{
    /**
     * @return bool
     */
    public function isRequestLoggingEnabled(): bool
    {
        return $this->get(SharedWebhookProcessorConfig::IS_REQUEST_LOGGING_ENABLED, false);
    }
}
