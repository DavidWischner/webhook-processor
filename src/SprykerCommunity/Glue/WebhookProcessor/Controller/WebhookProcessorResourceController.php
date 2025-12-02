<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor\Controller;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorFactory getFactory()
 */
class WebhookProcessorResourceController extends AbstractController
{
    /**
     * @Glue({
     *     "post": {
     *          "summary": [
     *              "Process webhook messages"
     *          ],
     *          "parameters": [{
     *              "ref": "acceptLanguage"
     *          }],
     *          "requestAttributesClassName": "Generated\\Shared\\Transfer\\RestWebhookProcessorRequestAttributesTransfer",
     *          "responseAttributesClassName": "Generated\\Shared\\Transfer\\RestWebhookProcessorResponseAttributesTransfer",
     *          "responses": {
     *              "400": "Bad request - No applicable processor found or validation error",
     *              "500": "Internal server error"
     *          }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function postAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->getFactory()
            ->createWebhookProcessor()
            ->processWebhook($restRequest);
    }
}
