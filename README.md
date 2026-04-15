# Spryker WebhookProcessor Module

A standalone Spryker module for receiving and routing webhook messages via a REST API endpoint. The module provides a flexible, plugin-based architecture for processing different webhook types and dispatching messages to RabbitMQ queues.

## Features

- REST API endpoint for receiving JSON/CloudEvents webhook messages
- Plugin-based processor architecture for flexible message handling
- Type-based routing to configurable message queues
- Pre-processor plugin stack per message type for transformation before queue dispatch
- CloudEvents format support (auto-transformed to JSON-API)
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

Then run:

```bash
composer require spryker-community/webhook-processor:^1.0.0
```

### 2. Generate transfer objects

```bash
vendor/bin/console transfer:generate
```

### 3. Register the Glue API resource

**`src/Pyz/Glue/GlueApplication/GlueApplicationDependencyProvider.php`**

```php
use SprykerCommunity\Glue\WebhookProcessor\Plugin\GlueApplication\WebhookProcessorResourceRoutePlugin;

protected function getResourceRoutePlugins(): array
{
    return [
        new WebhookProcessorResourceRoutePlugin(),
    ];
}
```

### 4. Register the event dispatcher plugin

**`src/Pyz/Glue/EventDispatcher/EventDispatcherDependencyProvider.php`**

```php
use SprykerCommunity\Glue\WebhookProcessor\Plugin\EventDispatcher\WebhookProcessorEventDispatcherPlugin;

protected function getEventDispatcherPlugins(): array
{
    return [
        new WebhookProcessorEventDispatcherPlugin(),
    ];
}
```

### 5. Configure the message type to queue mapping

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

### 6. (Optional) Register additional processor plugins

`QueueRouterProcessorPlugin` is registered by default and handles all queue-based routing. You only need to override `getProcessorPlugins()` if you want to add custom processors **before** the default one.

**`src/Pyz/Zed/WebhookProcessor/WebhookProcessorDependencyProvider.php`**

```php
use SprykerCommunity\Zed\WebhookProcessor\WebhookProcessorDependencyProvider as SprykerWebhookProcessorDependencyProvider;

class WebhookProcessorDependencyProvider extends SprykerWebhookProcessorDependencyProvider
{
    protected function getProcessorPlugins(): array
    {
        return array_merge(
            [
                new MyCustomProcessorPlugin(),
            ],
            parent::getProcessorPlugins(),
        );
    }
}
```

### 7. Configure queue adapters

**`config/Shared/config_default.php`**

```php
$config[QueueConstants::QUEUE_ADAPTER_CONFIGURATION] = [
    'product-webhook-queue' => [
        QueueConfig::CONFIG_QUEUE_ADAPTER => RabbitMqAdapter::class,
        QueueConfig::CONFIG_MAX_WORKER_NUMBER => 1,
    ],
];
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

**Error (400 Bad Request):**

```json
{
    "errors": [
        {
            "code": "400",
            "status": 400,
            "detail": "No applicable processor found for the webhook message"
        }
    ]
}
```

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

**2. Register in `WebhookProcessorDependencyProvider`:**

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

## Request logging

Incoming webhook requests can be logged for debugging purposes by setting the environment variable:

```
WEBHOOK_REQUEST_LOGGING_ENABLED=true
```

When enabled, every `POST /webhook-processor` request is logged at `INFO` level via Spryker's logger, including:

- Client IP
- All request headers
- Raw request body

The logger subscriber runs at priority **1024**, before the CloudEvents transformer (priority 512), so the original raw payload is always logged.

## Architecture

```
POST /webhook-processor
        ↓
WebhookProcessorRequestLoggerSubscriber (priority 1024)
  logs IP, headers, raw body if WEBHOOK_REQUEST_LOGGING_ENABLED=true
        ↓
WebhookProcessorRequestTransformerSubscriber (priority 512)
  CloudEvents → JSON-API transformation
        ↓
WebhookProcessorResourceController (Glue)
        ↓
WebhookProcessor (Glue) — validates, maps to WebhookMessageTransfer
        ↓
Zed Gateway — /webhook-processor/gateway/process-webhook
        ↓
WebhookProcessorFacade::processWebhook()
        ↓
WebhookProcessor (Business) — applies pre-processor plugins (transform/enrich)
        ↓
WebhookProcessor (Business) — iterates processor plugins (routing)
        ↓
QueueRouterProcessorPlugin::isApplicable() / process()
        ↓
WebhookProcessorFacade::routeToQueue()
        ↓
QueueRouter (Business) — routes to configured queue
        ↓
QueueSender — serializes message, dispatches via QueueClient
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
