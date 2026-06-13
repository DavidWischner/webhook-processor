<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor;

use Spryker\Shared\StorageRedis\StorageRedisConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerCommunity\Shared\WebhookProcessor\WebhookProcessorConstants;

class WebhookProcessorConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const string DEFAULT_QUEUE_POOL_NAME = 'synchronizationPool';

    /**
     * @var string
     */
    protected const string DEFAULT_REDIS_INBOX_KEY = 'webhook-processor:inbox';

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

    /**
     * @return string
     */
    public function getRedisHost(): string
    {
        return $this->get(StorageRedisConstants::STORAGE_REDIS_HOST, '');
    }

    /**
     * @return int
     */
    public function getRedisPort(): int
    {
        return (int)$this->get(StorageRedisConstants::STORAGE_REDIS_PORT, 6379);
    }

    /**
     * @return string
     */
    public function getRedisPassword(): string
    {
        return (string)$this->get(StorageRedisConstants::STORAGE_REDIS_PASSWORD, '');
    }

    /**
     * @return int
     */
    public function getRedisDatabase(): int
    {
        return (int)$this->get(StorageRedisConstants::STORAGE_REDIS_DATABASE, 1);
    }

    /**
     * @return string
     */
    public function getRedisInboxKey(): string
    {
        return $this->get(WebhookProcessorConstants::WEBHOOK_REDIS_INBOX_KEY, static::DEFAULT_REDIS_INBOX_KEY);
    }
}
