# Changelog

## [2.0.0] - 2026-06-24

### Added
- `WebhookProcessorResourcePlugin` (`SprykerCommunity\Glue\WebhookProcessor\Plugin\GlueBackendApiApplication`) — registers `POST /webhook-processor` on Spryker's `GlueBackend` application, which runs with full Zed environment variables and routes messages directly to RabbitMQ via `WebhookProcessorFacade::processWebhook()`, without a synchronous Zed gateway call or Redis buffer
- `WebhookProcessorResourceController` — parses CloudEvents and JSON-API request bodies, maps to `WebhookMessageTransfer`, calls the Zed facade in-process, returns `GlueResponseTransfer`
- `WebhookProcessorFactory` + `WebhookProcessorDependencyProvider` — wires `WebhookProcessorFacadeInterface` via the Backend container locator

### Removed
- Entire Classic-Glue dispatcher stack: `WebhookProcessorResourceRoutePlugin`, `WebhookProcessorEventDispatcherPlugin`, `WebhookProcessorRequestTransformerSubscriber`, `WebhookProcessorRequestLoggerSubscriber`, `WebhookProcessor` (Glue), `WebhookMessageDispatcherPluginInterface`, `ZedGatewayDispatcherPlugin`, `RedisInboxDispatcherPlugin`, `WebhookRedisBuffer`, `WebhookProcessorFactory` (classic), `WebhookProcessorDependencyProvider` (classic), `WebhookProcessorConfig` (Glue), and all Glue-layer Dependency/Mapper/Processor/RestResponseBuilder/Validator classes. The GlueBackend dispatcher (`WebhookProcessorResourcePlugin`) is now the only supported endpoint.

## [1.2.0] - 2026-06-18

### Added
- Pluggable Glue-side message dispatcher via `WebhookMessageDispatcherPluginInterface` — `WebhookProcessorDependencyProvider::getWebhookMessageDispatcherPlugin()` can be overridden in a project to dispatch messages differently (e.g. to a buffer/queue) instead of forwarding to the Zed gateway
- `ZedGatewayDispatcherPlugin` — default dispatcher plugin, forwards messages to the Zed backend-gateway (same `429`/`500` behaviour as before)
- `RedisInboxDispatcherPlugin` + `WebhookRedisBuffer` (Glue) — built-in fire-and-forget dispatcher that pushes webhook messages onto a Redis list ("inbox") and returns `200`/`500` immediately, decoupling the endpoint's response time from backend processing capacity
- `WebhookInboxWorker` + `WebhookInboxWorkerConsole` (Zed) — new `webhook-processor:inbox-worker:start` console command that drains the Redis inbox and runs each buffered message through the same pre-processor/processor plugin stack as the synchronous gateway flow
- `WebhookProcessorFacade::runWebhookInboxWorker()`
- `WebhookProcessorConstants::WEBHOOK_REDIS_INBOX_KEY` and Redis connection config getters (`getRedisHost()`, `getRedisPort()`, `getRedisPassword()`, `getRedisDatabase()`, `getRedisInboxKey()`) on Glue and Zed `WebhookProcessorConfig`, reusing `StorageRedisConstants`
- Error logging via Spryker's logger: Redis push failures in `RedisInboxDispatcherPlugin` (Glue), and message-decode/routing/connection failures in `WebhookInboxWorker` (Zed) — failures on the async path are no longer silent
- README documentation for the new dispatcher-plugin extension point, the Redis-inbox dispatcher, and its response-semantics trade-off (always `200 OK`, no delivery feedback to the sender)

### Changed
- `WebhookProcessor` (Glue) now delegates response building entirely to the configured `WebhookMessageDispatcherPluginInterface` plugin instead of calling the Zed gateway directly
- `WebhookInboxWorker` drains the Redis inbox via a blocking `BRPOP` instead of polling with `RPOP` + a fixed sleep, removing idle CPU/network overhead and reducing worst-case message pickup latency
- `WebhookInboxWorker` now reconnects and backs off on a lost Redis connection instead of aborting the run; a single malformed or unroutable buffered message is logged and skipped instead of stopping the rest of the run
- Glue and Zed Redis connections are now persistent (`pconnect`) instead of reconnecting on every request/worker run

## [1.1.0] - 2026-06-02

### Added
- Rate limiting / back-pressure via `429 Too Many Requests`: configurable `WEBHOOK_ZED_TIMEOUT` (via `WebhookProcessorConstants::WEBHOOK_ZED_TIMEOUT`) for the synchronous ZedRequest to the backend-gateway; on timeout the endpoint returns `429` instead of blocking indefinitely

## [1.0.3] - 2026-04-16

### Fixed
- `WebhookMessageMapper`: CloudEvents envelope fields (`id`, `source`, `specversion`, `subject`, `time`, `datacontenttype`, `dataschema`) are now correctly collected into `WebhookMessageTransfer::metadata`
- `WebhookMessageMapper`: payload resolution now falls back to the JSON-API `payload` field when `data` is empty, so both CloudEvents and plain JSON-API requests are handled correctly

## [1.0.2] - 2026-04-14

### Added
- `WebhookProcessorRequestLoggerSubscriber` — logs incoming webhook requests (IP, headers, body) when the env variable `WEBHOOK_REQUEST_LOGGING_ENABLED=true` is set; runs at priority 1024 (before the CloudEvents transformer)

## [1.0.1] - 2026-04-14

### Fixed
- Set `Accept: application/vnd.api+json` header if missing — required by Spryker Glue, but not sent by some platforms (e.g. Akeneo)
- Set `Content-Type: application/vnd.api+json` header during CloudEvents → JSON-API transformation

## [1.0.0] - 2026-03-04

### Added
- REST API endpoint `POST /webhook-processor` for receiving webhook messages
- CloudEvents format support with automatic transformation to JSON-API
- Plugin-based processor architecture (`WebhookProcessorPluginInterface`)
- `QueueRouterProcessorPlugin` as the default processor — routes messages to queues based on configurable type-to-queue mapping
- Pre-processor plugin stack (`WebhookPreProcessorPluginInterface`) — applied per message type before queue dispatch
- `WebhookProcessorRequestTransformerSubscriber` for early CloudEvents → JSON-API transformation (priority 512)
- Transfer objects: `WebhookMessageTransfer`, `WebhookProcessorResponseTransfer`, `WebhookProcessorErrorTransfer`, `RestWebhookProcessorRequestAttributesTransfer`, `RestWebhookProcessorResponseAttributesTransfer`, `WebhookProcessorGatewayRequestTransfer`, `WebhookProcessorGatewayResponseTransfer`
- `QueueRouter` business class encapsulating routing and pre-processing logic
- `DEFAULT_QUEUE_POOL_NAME` constant in `WebhookProcessorConfig`
