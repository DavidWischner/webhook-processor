<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Plugin\RequestBuilder;

use Generated\Shared\Transfer\GlueRequestTransfer;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\RequestBuilderPluginInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Shared\Log\LoggerTrait;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig getConfig()
 */
class WebhookProcessorRequestLoggerPlugin extends AbstractPlugin implements RequestBuilderPluginInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const string RESOURCE_PATH_SEGMENT = 'webhook-processor';

    /**
     * @param \Generated\Shared\Transfer\GlueRequestTransfer $glueRequestTransfer
     *
     * @return \Generated\Shared\Transfer\GlueRequestTransfer
     */
    public function build(GlueRequestTransfer $glueRequestTransfer): GlueRequestTransfer
    {
        if (!$this->getConfig()->isRequestLoggingEnabled()) {
            return $glueRequestTransfer;
        }

        if (!str_contains($glueRequestTransfer->getPath() ?? '', static::RESOURCE_PATH_SEGMENT)) {
            return $glueRequestTransfer;
        }

        if ($glueRequestTransfer->getMethod() !== 'POST') {
            return $glueRequestTransfer;
        }

        $this->getLogger()->info('Webhook request received.', [
            'headers' => $glueRequestTransfer->getMeta(),
            'body' => $glueRequestTransfer->getContent(),
        ]);

        return $glueRequestTransfer;
    }
}
