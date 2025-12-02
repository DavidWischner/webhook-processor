# Changelog

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
