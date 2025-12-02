<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Service;

use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;

class WebhookProcessorToUtilEncodingServiceBridge implements WebhookProcessorToUtilEncodingServiceInterface
{
    /**
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        protected UtilEncodingServiceInterface $utilEncodingService,
    ) {
    }

    /**
     * @param mixed $value
     *
     * @return string|null
     */
    public function encodeJson(mixed $value): ?string
    {
        return $this->utilEncodingService->encodeJson($value);
    }
}
