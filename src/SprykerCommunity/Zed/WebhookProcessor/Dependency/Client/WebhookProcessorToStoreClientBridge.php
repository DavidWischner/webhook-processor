<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Client;

use Spryker\Client\Store\StoreClientInterface;

class WebhookProcessorToStoreClientBridge implements WebhookProcessorToStoreClientInterface
{
    /**
     * @param \Spryker\Client\Store\StoreClientInterface $storeClient
     */
    public function __construct(
        protected StoreClientInterface $storeClient,
    ) {
    }

    /**
     * @return string
     */
    public function getCurrentStore(): string
    {
        return $this->storeClient->getCurrentStore()->getName();
    }
}
