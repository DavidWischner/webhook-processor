<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Redis;

class WebhookProcessorToRedisBridge implements WebhookProcessorToRedisInterface
{
    /**
     * @var \Redis|null
     */
    protected ?\Redis $connection = null;

    /**
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $database
     */
    public function __construct(
        protected string $host,
        protected int $port,
        protected string $password,
        protected int $database,
    ) {
    }

    /**
     * @param string $listKey
     * @param int $blockingTimeoutSeconds
     *
     * @throws \RedisException
     *
     * @return string|null
     */
    public function pop(string $listKey, int $blockingTimeoutSeconds): ?string
    {
        try {
            $result = $this->getConnection()->brPop([$listKey], $blockingTimeoutSeconds);
        } catch (\RedisException $redisException) {
            // The connection may be in a broken state after a read/write error; drop it so the next call reconnects.
            $this->connection = null;

            throw $redisException;
        }

        if (!is_array($result) || $result === []) {
            return null;
        }

        return $result[1] ?? null;
    }

    /**
     * @return \Redis
     */
    protected function getConnection(): \Redis
    {
        if ($this->connection === null) {
            $redis = new \Redis();

            // Reuses an existing TCP connection across consecutive cron invocations of the
            // worker process instead of paying the connect/AUTH/SELECT round-trip every minute.
            $redis->pconnect($this->host, $this->port, 0, sprintf('webhook-processor:%d', $this->database));

            if ($this->password !== '') {
                $redis->auth($this->password);
            }

            $redis->select($this->database);
            $this->connection = $redis;
        }

        return $this->connection;
    }
}
