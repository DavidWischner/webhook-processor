<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\AbstractBundleConfig;
use Spryker\Shared\StorageRedis\StorageRedisConstants;
use SprykerCommunity\Shared\WebhookProcessor\WebhookProcessorConstants;

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
     * @var string
     */
    protected const string DEFAULT_REDIS_INBOX_KEY = 'webhook-processor:inbox';

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

    /**
     * @return int
     */
    public function getZedRequestTimeout(): int
    {
        return $this->get(WebhookProcessorConstants::WEBHOOK_ZED_TIMEOUT, 0);
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
