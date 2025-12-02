<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class WebhookProcessorConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const string DEFAULT_QUEUE_POOL_NAME = 'synchronizationPool';

    /**
     * Specification:
     * - Returns a mapping of webhook message types to queue names.
     * - Key: message type (e.g., 'product.updated')
     * - Value: queue name (e.g., 'product-queue')
     * - Override this method in project-level config to provide custom mappings.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getMessageTypeToQueueMapping(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Returns the default queue pool name for webhook messages.
     * - Can be overridden in project-level config.
     *
     * @api
     *
     * @return string
     */
    public function getDefaultQueuePoolName(): string
    {
        return static::DEFAULT_QUEUE_POOL_NAME;
    }
}
