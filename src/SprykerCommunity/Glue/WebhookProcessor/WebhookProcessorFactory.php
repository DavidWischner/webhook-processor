<?php

declare(strict_types=1);

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerCommunity\Glue\WebhookProcessor;

use Spryker\Glue\Kernel\AbstractFactory;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientInterface;
use SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapper;
use SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface;
use SprykerCommunity\Glue\WebhookProcessor\Processor\WebhookProcessor;
use SprykerCommunity\Glue\WebhookProcessor\Processor\WebhookProcessorInterface;
use SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilder;
use SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface;
use SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidator;
use SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface;

/**
 * @method \SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorConfig getConfig()
 * @method \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface getResourceBuilder()
 */
class WebhookProcessorFactory extends AbstractFactory
{
    /**
     * @return \SprykerCommunity\Glue\WebhookProcessor\Processor\WebhookProcessorInterface
     */
    public function createWebhookProcessor(): WebhookProcessorInterface
    {
        return new WebhookProcessor(
            $this->createWebhookMessageMapper(),
            $this->createRequestValidator(),
            $this->createRestResponseBuilder(),
            $this->getZedRequestClient(),
        );
    }

    /**
     * @return \SprykerCommunity\Glue\WebhookProcessor\Mapper\WebhookMessageMapperInterface
     */
    public function createWebhookMessageMapper(): WebhookMessageMapperInterface
    {
        return new WebhookMessageMapper();
    }

    /**
     * @return \SprykerCommunity\Glue\WebhookProcessor\Validator\WebhookProcessorRequestValidatorInterface
     */
    public function createRequestValidator(): WebhookProcessorRequestValidatorInterface
    {
        return new WebhookProcessorRequestValidator();
    }

    /**
     * @return \SprykerCommunity\Glue\WebhookProcessor\RestResponseBuilder\WebhookProcessorRestResponseBuilderInterface
     */
    public function createRestResponseBuilder(): WebhookProcessorRestResponseBuilderInterface
    {
        return new WebhookProcessorRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    /**
     * @return \SprykerCommunity\Glue\WebhookProcessor\Dependency\Client\WebhookProcessorToZedRequestClientInterface
     */
    public function getZedRequestClient(): WebhookProcessorToZedRequestClientInterface
    {
        return $this->getProvidedDependency(WebhookProcessorDependencyProvider::CLIENT_ZED_REQUEST);
    }
}
