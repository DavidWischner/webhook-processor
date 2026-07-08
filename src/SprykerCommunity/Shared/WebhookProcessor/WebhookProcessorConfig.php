<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Shared\WebhookProcessor;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class WebhookProcessorConfig extends AbstractSharedConfig
{
    /**
     * Feature flag — enable verbose request logging for the webhook endpoint.
     * Set via env variable WEBHOOK_REQUEST_LOGGING_ENABLED.
     *
     * @var string
     */
    public const string IS_REQUEST_LOGGING_ENABLED = 'WEBHOOK_PROCESSOR:IS_REQUEST_LOGGING_ENABLED';
}
