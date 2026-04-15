# Changelog

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
