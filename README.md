# Spryker WebhookProcessor Module

A standalone Spryker module for receiving and routing webhook messages via a REST API endpoint. The module provides a flexible, plugin-based architecture for processing different webhook types and dispatching messages to RabbitMQ queues.

## Features

- REST API endpoint for receiving JSON/CloudEvents webhook messages
- Pluggable Glue-side message dispatcher (`WebhookMessageDispatcherPluginInterface`) — defaults to forwarding to the Zed gateway, swappable for custom transports (e.g. buffers, queues)
- Built-in Redis-inbox dispatcher (`RedisInboxDispatcherPlugin` + `WebhookInboxWorkerConsole`) for fire-and-forget buffering under heavy webhook load (see [Redis-inbox dispatcher](#redis-inbox-dispatcher-fire-and-forget-buffering))
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

### Custom webhook message dispatcher plugins

After validation and mapping, the Glue `WebhookProcessor` hands the `WebhookMessageTransfer` to a single `WebhookMessageDispatcherPluginInterface` plugin, which builds the REST response. By default this is `ZedGatewayDispatcherPlugin`, which forwards the message to the Zed backend-gateway (see [Rate limiting / Back-pressure via 429](#rate-limiting--back-pressure-via-429)). Override this plugin to dispatch messages differently — e.g. to a buffer, cache or queue — without forwarding to Zed at all.

A ready-to-use alternative, `RedisInboxDispatcherPlugin`, is included with the module — see [Redis-inbox dispatcher](#redis-inbox-dispatcher-fire-and-forget-buffering).

**1. Implement the interface:**

```php
use Generated\Shared\Transfer\WebhookMessageTransfer;
use Generated\Shared\Transfer\WebhookProcessorResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\Kernel\AbstractPlugin;
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;

class MyCustomDispatcherPlugin extends AbstractPlugin implements WebhookMessageDispatcherPluginInterface
{
    public function dispatch(WebhookMessageTransfer $webhookMessageTransfer): RestResponseInterface
    {
        // custom dispatch logic, e.g. push to a buffer/queue ...

        return $this->getFactory()->createRestResponseBuilder()->createSuccessResponse(
            (new WebhookProcessorResponseTransfer())
                ->setSuccess(true)
                ->setMessage('Webhook accepted')
                ->setProcessedBy(static::class),
        );
    }
}
```

**2. Register it in `WebhookProcessorDependencyProvider` (Glue):**

```php
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorDependencyProvider as SprykerWebhookProcessorDependencyProvider;

class WebhookProcessorDependencyProvider extends SprykerWebhookProcessorDependencyProvider
{
    protected function getWebhookMessageDispatcherPlugin(): WebhookMessageDispatcherPluginInterface
    {
        return new MyCustomDispatcherPlugin();
    }
}
```

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

## Rate limiting / Back-pressure via 429

This applies to the default `ZedGatewayDispatcherPlugin` (see [Custom webhook message dispatcher plugins](#custom-webhook-message-dispatcher-plugins)). The module supports responding with `429 Too Many Requests` when the backend-gateway is under heavy load.

### How it works

Every webhook call blocks a Glue worker while it waits for the synchronous ZedRequest to the backend-gateway to complete. If the gateway is overloaded and exceeds the configured timeout, the connection times out and the endpoint returns 429.

### Configuration

In `config_default.php`:

```php
use SprykerCommunity\Shared\WebhookProcessor\WebhookProcessorConstants;

$config[WebhookProcessorConstants::WEBHOOK_ZED_TIMEOUT] = (int)getenv('WEBHOOK_ZED_TIMEOUT') ?: 0;
```

Set the env var `WEBHOOK_ZED_TIMEOUT` to the desired timeout in seconds:

| Value | Behaviour |
|---|---|
| `0` (default) | No timeout — the call blocks until the gateway responds or the PHP request timeout is reached. 429 is never returned. |
| `> 0` | If the gateway does not respond within N seconds, the Glue endpoint returns 429. |

A value of `3`–`5` seconds is recommended for production. This should be well above the gateway's normal response time under low load (typically < 500 ms) but short enough to signal back-pressure before sender considers the webhook endpoint unreliable.

### Response on timeout

```json
{
    "errors": [
        {
            "code": "429",
            "status": 429,
            "detail": "Gateway timeout"
        }
    ]
}
```

Other gateway errors (e.g. SSL issues, invalid responses) result in `500 Internal Server Error` and do **not** trigger a 429.

## Redis-inbox dispatcher (fire-and-forget buffering)

`RedisInboxDispatcherPlugin` is a built-in alternative to the default `ZedGatewayDispatcherPlugin` (see [Custom webhook message dispatcher plugins](#custom-webhook-message-dispatcher-plugins)). Instead of forwarding each webhook synchronously to the Zed backend-gateway, it pushes the message onto a Redis list ("inbox") and returns immediately. A Zed cron job (`WebhookInboxWorkerConsole`) continuously pops messages from that list and routes them to their configured queues.

This decouples the Glue endpoint's response time from the backend's processing capacity entirely, which is useful when the sender (e.g. Akeneo) issues many webhooks in short bursts and the 429-based back-pressure strategy (see [Rate limiting / Back-pressure via 429](#rate-limiting--back-pressure-via-429)) is not desirable or sufficient.

> Requires the `ext-redis` PHP extension (PhpRedis).

### Architecture

```
Glue:

POST /webhook-processor
        ↓
WebhookProcessor (Glue) — validates, maps to WebhookMessageTransfer
        ↓
RedisInboxDispatcherPlugin::dispatch()
        ↓
WebhookRedisBuffer::push() — JSON-encodes the transfer, LPUSH onto the Redis inbox list
        ↓
200 OK (push succeeded) / 500 Internal Server Error (Redis unreachable)


Zed (cron, every minute):

webhook-processor:inbox-worker:start --time-limit=55
        ↓
WebhookInboxWorker::run() — BRPOP loop (1s blocking timeout) until the time limit is reached
        ↓
WebhookMessageTransfer::fromArray() — JSON-decodes the popped value
        ↓
WebhookProcessor (Business) — same pre-processor + processor plugin stack as the gateway flow
        ↓
QueueRouterProcessorPlugin → QueueRouter (Business) — routes to the configured queue
```

### Configuration

The Redis connection reuses Spryker's generic storage-Redis configuration (`StorageRedisConstants`), so no additional setup is needed if a Redis store is already configured for the project.

**`config/Shared/config_default.php`** (only needed if not already configured):

```php
use Spryker\Shared\StorageRedis\StorageRedisConstants;

$config[StorageRedisConstants::STORAGE_REDIS_HOST] = getenv('SPRYKER_STORAGE_REDIS_HOST');
$config[StorageRedisConstants::STORAGE_REDIS_PORT] = (int)getenv('SPRYKER_STORAGE_REDIS_PORT');
$config[StorageRedisConstants::STORAGE_REDIS_PASSWORD] = getenv('SPRYKER_STORAGE_REDIS_PASSWORD') ?: '';
$config[StorageRedisConstants::STORAGE_REDIS_DATABASE] = (int)getenv('SPRYKER_STORAGE_REDIS_DATABASE') ?: 1;
```

Optionally override the Redis list key used as the inbox (defaults to `webhook-processor:inbox`):

```php
use SprykerCommunity\Shared\WebhookProcessor\WebhookProcessorConstants;

$config[WebhookProcessorConstants::WEBHOOK_REDIS_INBOX_KEY] = 'webhook-processor:inbox';
```

### 1. Register the dispatcher plugin (Glue)

**`src/Pyz/Glue/WebhookProcessor/WebhookProcessorDependencyProvider.php`**

```php
use SprykerCommunity\Glue\WebhookProcessor\Dependency\Plugin\WebhookMessageDispatcherPluginInterface;
use SprykerCommunity\Glue\WebhookProcessor\Plugin\WebhookMessageDispatcher\RedisInboxDispatcherPlugin;
use SprykerCommunity\Glue\WebhookProcessor\WebhookProcessorDependencyProvider as SprykerWebhookProcessorDependencyProvider;

class WebhookProcessorDependencyProvider extends SprykerWebhookProcessorDependencyProvider
{
    protected function getWebhookMessageDispatcherPlugin(): WebhookMessageDispatcherPluginInterface
    {
        return new RedisInboxDispatcherPlugin();
    }
}
```

### 2. Register the inbox-worker console command (Zed)

**`src/Pyz/Zed/Console/ConsoleDependencyProvider.php`**

```php
use SprykerCommunity\Zed\WebhookProcessor\Communication\Console\WebhookInboxWorkerConsole;

protected function getConsoleCommands(Container $container): array
{
    $commands = parent::getConsoleCommands($container);
    $commands[] = new WebhookInboxWorkerConsole();

    return $commands;
}
```

### 3. Schedule the inbox worker via cron

`webhook-processor:inbox-worker:start` is a long-running, time-limited command. Run it once per minute with a `--time-limit` slightly below the schedule interval so consecutive runs don't overlap (default time limit: `60` seconds):

```php
$jobs[] = [
    'name' => 'webhook-processor-inbox-worker',
    'command' => '$PHP_BIN vendor/bin/console webhook-processor:inbox-worker:start --time-limit=55 -vv',
    'schedule' => '* * * * *',
    'enable' => true,
    'stores' => $allStores,
];
```

### Response behaviour

> **Trade-off: availability over delivery feedback.** Switching `getWebhookMessageDispatcherPlugin()` (Glue) from `ZedGatewayDispatcherPlugin` to `RedisInboxDispatcherPlugin` changes what the endpoint's HTTP response actually means. With the default plugin, `200`/`422`/`429`/`500` reflect whether the message was *processed*. With the Redis-inbox plugin, the response only ever reflects whether the message was *accepted into the buffer* — processing happens later, out-of-band, and its outcome is never sent back to the caller. Choose this plugin when the sender's own retry/back-off behaviour under `429` is unreliable and causes it to give up and drop webhooks entirely (the motivating case here: an upstream sender that stops sending after repeated `429`s under load, causing data loss) — i.e. when "always accept, process best-effort" is preferable to "sometimes reject, but the sender retries reliably". If the sender retries correctly on non-2xx responses, prefer `ZedGatewayDispatcherPlugin` with a tuned `WEBHOOK_ZED_TIMEOUT` instead, since it preserves end-to-end delivery feedback.

Unlike the default `ZedGatewayDispatcherPlugin`, this dispatcher never blocks on the backend and is **not** subject to the 429 back-pressure described in [Rate limiting / Back-pressure via 429](#rate-limiting--back-pressure-via-429):

| Outcome | Response |
|---|---|
| Message successfully pushed to the Redis inbox | `200 OK` |
| Redis unreachable / push failed | `500 Internal Server Error` (also logged via Spryker's logger, see below) |

Processing failures further down the pipeline (e.g. an unroutable message type, queue send failure, malformed buffered payload) are **not** reflected back to the original webhook caller — the Glue response only confirms that the message was buffered, not that it was processed. These failures are logged via Spryker's logger (by `WebhookInboxWorker` and `RedisInboxDispatcherPlugin`) instead. Since the webhook sender will never see or retry them, route the inbox-worker's error logs to active monitoring/alerting — without it, processing failures are silent.

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
WebhookMessageDispatcherPluginInterface::dispatch() (Glue)
        ↓  (default: ZedGatewayDispatcherPlugin, timeout → 429 if WEBHOOK_ZED_TIMEOUT_SECONDS > 0)
        ↓  (alternative: RedisInboxDispatcherPlugin, see "Redis-inbox dispatcher" section)
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
