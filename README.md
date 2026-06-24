# Spryker WebhookProcessor Module

A standalone Spryker module for receiving and processing webhook messages via a REST API endpoint on the GlueBackend application. The module provides a plugin-based architecture for handling different webhook types — what happens with a message after it arrives is entirely up to the registered processor plugins.

## Features

- `POST /webhook-processor` endpoint on Spryker's `GlueBackend` application — runs with full Zed environment variables, calls `WebhookProcessorFacade` in-process, no gateway roundtrip required
- CloudEvents and JSON-API request body support
- Plugin-based processor architecture (`WebhookProcessorPluginInterface`) — implement any processing logic per message type
- Pre-processor plugin stack per message type for transformation/enrichment before processing
- Built-in `QueueRouterProcessorPlugin` for type-based routing to RabbitMQ queues (optional, not required)
- Fully standalone and installable via Composer

## Installation

### 1. Add the repository to `composer.json` and install

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/DavidWischner/webhook-processor"
        }
    ]
}
```

```bash
composer require spryker-community/webhook-processor:^1.0.0
```

### 2. Generate transfer objects

```bash
vendor/bin/console transfer:generate
```

### 3. (Optional) Configure the message type to queue mapping

Required only if you use the built-in `QueueRouterProcessorPlugin`. Skip this step if you implement your own processor plugin.

**`src/Pyz/Zed/WebhookProcessor/WebhookProcessorConfig.php`**

```php
use SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorConfig as SprykerWebhookProcessorConfig;

class WebhookProcessorConfig extends SprykerWebhookProcessorConfig
{
    public function getMessageTypeToQueueMapping(): array
    {
        return [
            'com.example.product.updated' => 'product-webhook-queue',
            'com.example.order.created'   => 'order-webhook-queue',
        ];
    }
}
```

### 4. (Optional) Configure queue adapters

Required only if you use the built-in `QueueRouterProcessorPlugin`.

**`config/Shared/config_default.php`**

```php
$config[QueueConstants::QUEUE_ADAPTER_CONFIGURATION] = [
    'product-webhook-queue' => [
        QueueConfig::CONFIG_QUEUE_ADAPTER => RabbitMqAdapter::class,
        QueueConfig::CONFIG_MAX_WORKER_NUMBER => 1,
    ],
];
```

### 5. Set up the GlueBackend application (skip if already done)

**`deploy.yml`**

```yaml
groups:
  EU:
    region: EU
    applications:
      glue_backend_eu:
        application: glue-backend
        endpoints:
          glue-backend.de.myshop.com:
            store: DE
        limits:
          workers: 16
```

**`public/GlueBackend/index.php`**

```php
<?php

use Spryker\Glue\GlueApplication\Bootstrap\GlueBootstrap;
use Spryker\Shared\Config\Application\Environment;
use Spryker\Shared\ErrorHandler\ErrorHandlerEnvironment;

define('APPLICATION', 'GLUE_BACKEND');
defined('APPLICATION_ROOT_DIR') || define('APPLICATION_ROOT_DIR', dirname(__DIR__, 2));

require_once APPLICATION_ROOT_DIR . '/vendor/autoload.php';

Environment::initialize();

$errorHandlerEnvironment = new ErrorHandlerEnvironment();
$errorHandlerEnvironment->initialize();

$bootstrap = new GlueBootstrap();
$bootstrap->boot()->run();
```

### 6. Verify bootstrap and router plugins (skip if already done)

These plugins ship with Spryker's default `GlueApplicationDependencyProvider` and are present in most projects. Check that all four are registered — if they are, skip this step.

**Important:** `BackendApiGlueApplicationBootstrapPlugin` must be returned **before** `FallbackStorefrontApiGlueApplicationBootstrapPlugin`, which always returns `true` for `isServing()` and would otherwise prevent the backend bootstrap from being selected.

**`src/Pyz/Glue/GlueApplication/GlueApplicationDependencyProvider.php`**

```php
use Spryker\Glue\GlueBackendApiApplication\Plugin\GlueApplication\BackendApiGlueApplicationBootstrapPlugin;
use Spryker\Glue\GlueBackendApiApplication\Plugin\GlueApplication\BackendRouterProviderPlugin;
use Spryker\Glue\GlueBackendApiApplication\Plugin\GlueApplication\ResourcesProviderPlugin as BackendResourcesProviderPlugin;
use Spryker\Glue\GlueBackendApiApplication\Plugin\GlueApplication\CustomRouteRoutesProviderPlugin as BackendCustomRouteRoutesProviderPlugin;

// getGlueApplicationBootstrapPlugins() — before FallbackStorefrontApiGlueApplicationBootstrapPlugin:
new BackendApiGlueApplicationBootstrapPlugin(),

// getGlueApplicationRouterProviderPlugins():
new BackendRouterProviderPlugin(),

// getResourcesProviderPlugins():
new BackendResourcesProviderPlugin(),

// getRoutesProviderPlugins():
new BackendCustomRouteRoutesProviderPlugin(),
```

### 7. Create `GlueBackendApiApplicationDependencyProvider` (skip if already done)

If your project already has this file, skip to step 8.

**`src/Pyz/Glue/GlueBackendApiApplication/GlueBackendApiApplicationDependencyProvider.php`**

```php
use Spryker\Glue\GlueBackendApiApplication\GlueBackendApiApplicationDependencyProvider as SprykerGlueBackendApiApplicationDependencyProvider;

class GlueBackendApiApplicationDependencyProvider extends SprykerGlueBackendApiApplicationDependencyProvider
{
}
```

### 8. Register the resource plugin

**`src/Pyz/Glue/GlueBackendApiApplication/GlueBackendApiApplicationDependencyProvider.php`**

```php
use Spryker\Glue\GlueBackendApiApplication\GlueBackendApiApplicationDependencyProvider as SprykerGlueBackendApiApplicationDependencyProvider;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\GlueBackendApiApplication\WebhookProcessorResourcePlugin;

class GlueBackendApiApplicationDependencyProvider extends SprykerGlueBackendApiApplicationDependencyProvider
{
    protected function getResourcePlugins(): array
    {
        return [
            new WebhookProcessorResourcePlugin(),
        ];
    }
}
```

## Usage

### API endpoint

```
POST /webhook-processor
Content-Type: application/json
```

### Supported input formats

**CloudEvents format** (recommended):

```json
{
    "type": "com.example.product.updated",
    "id": "abc-123",
    "source": "https://example.com",
    "specversion": "1.0",
    "data": {
        "productId": "12345",
        "sku": "ABC-123"
    }
}
```

**JSON-API format**:

```json
{
    "data": {
        "type": "webhook-processor",
        "attributes": {
            "type": "com.example.product.updated",
            "payload": { "productId": "12345" }
        }
    }
}
```

### Response

**Success (200 OK):**

```json
{
    "data": {
        "type": "webhook-processor",
        "id": null,
        "attributes": {
            "success": true,
            "message": "Message successfully routed to queue: product-webhook-queue",
            "processedBy": "SprykerCommunity\\Zed\\WebhookProcessor\\Business\\Router\\QueueRouter"
        }
    }
}
```

| Outcome | HTTP status |
|---|---|
| Message successfully routed to queue | `200 OK` |
| Missing or invalid JSON body | `400 Bad Request` |
| Missing message type field | `400 Bad Request` |
| No processor found for the message type | `422 Unprocessable Entity` |

## Extension

### Custom pre-processor plugins

Pre-processors transform, validate or enrich a message before it is handed to the processor plugins. All applicable pre-processors run first, in registration order.

**1. Implement the interface:**

```php
use Generated\Shared\Transfer\WebhookMessageTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookPreProcessorPluginInterface;

class MyEnricherPlugin extends AbstractPlugin implements WebhookPreProcessorPluginInterface
{
    public function isApplicable(WebhookMessageTransfer $webhookMessageTransfer): bool
    {
        return $webhookMessageTransfer->getType() === 'com.example.product.updated';
    }

    public function process(WebhookMessageTransfer $webhookMessageTransfer): WebhookMessageTransfer
    {
        $payload = $webhookMessageTransfer->getPayload();
        $payload['enriched_at'] = date('Y-m-d H:i:s');
        $webhookMessageTransfer->setPayload($payload);

        return $webhookMessageTransfer;
    }
}
```

**2. Register in `WebhookProcessorDependencyProvider` (Zed):**

```php
protected function getPreProcessorPlugins(): array
{
    return [
        new MyEnricherPlugin(),
    ];
}
```

### Custom processor plugins

Custom processors allow alternative processing logic beyond queue routing.

```php
use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerCommunity\Zed\WebhookProcessor\Dependency\Plugin\WebhookProcessorPluginInterface;

class MyCustomProcessorPlugin extends AbstractPlugin implements WebhookProcessorPluginInterface
{
    public function isApplicable(WebhookMessageTransfer $webhookMessageTransfer): bool
    {
        return $webhookMessageTransfer->getType() === 'com.example.custom.action';
    }

    public function process(WebhookMessageTransfer $webhookMessageTransfer): WebhookProcessorResponseTransfer
    {
        // custom logic ...

        return (new WebhookProcessorResponseTransfer())
            ->setSuccess(true)
            ->setMessage('Processed successfully')
            ->setProcessedBy(static::class);
    }
}
```

Register it before the default `QueueRouterProcessorPlugin` using `array_merge`:

```php
protected function getProcessorPlugins(): array
{
    return array_merge(
        [
            new MyCustomProcessorPlugin(),
        ],
        parent::getProcessorPlugins(),
    );
}
```

## Architecture

```
POST /webhook-processor   (application: glue-backend)
        ↓
WebhookProcessorResourceController::postAction()
  parses CloudEvents / JSON-API body → WebhookMessageTransfer
        ↓
WebhookProcessorFacade::processWebhook()   (Zed facade, called in-process)
        ↓
WebhookProcessor (Business) — pre-processor plugin stack (WebhookPreProcessorPluginInterface)
  transform / enrich / validate per message type
        ↓
WebhookProcessorPluginInterface::isApplicable() / process()   ← your custom plugins go here
        ↓ (built-in default)
QueueRouterProcessorPlugin → QueueRouter → QueueSender → RabbitMQ
```

## Troubleshooting

**"No applicable processor found"**
- Ensure `QueueRouterProcessorPlugin` is registered in `WebhookProcessorDependencyProvider::getProcessorPlugins()`
- Verify the message `type` is present in `WebhookProcessorConfig::getMessageTypeToQueueMapping()`

**"Queue send failed"**
- Confirm the queue exists in `config_default.php` and RabbitMQ is reachable
- Check for typos in queue names

**Transfer objects not found**
```bash
vendor/bin/console transfer:generate
```

## License

MIT — see [LICENSE](LICENSE).
