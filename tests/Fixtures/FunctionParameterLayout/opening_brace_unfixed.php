<?php

declare(strict_types = 1);

final class StripeEventSanitizer
{
    public function sanitize(
        FormProvider $provider,
        StripeWebhookEventType $eventType,
        Event $event,
    ): StripeEventData {
        return StripeEventData::fromEvent($provider, $eventType, $event);
    }
}
