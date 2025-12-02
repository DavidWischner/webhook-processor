<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Zed\WebhookProcessor\Dependency\Service;

interface WebhookProcessorToUtilEncodingServiceInterface
{
    /**
     * @param mixed $value
     *
     * @return string|null
     */
    public function encodeJson(mixed $value): ?string;
}
