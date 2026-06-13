<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Dependency\Redis;

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
     * @param string $value
     *
     * @return void
     */
    public function push(string $listKey, string $value): void
    {
        $this->getConnection()->lPush($listKey, $value);
    }

    /**
     * @return \Redis
     */
    protected function getConnection(): \Redis
    {
        if ($this->connection === null) {
            $redis = new \Redis();

            // Reuses an existing TCP connection across requests on the same PHP-FPM worker
            // instead of paying the connect/AUTH/SELECT round-trip on every single webhook call.
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
