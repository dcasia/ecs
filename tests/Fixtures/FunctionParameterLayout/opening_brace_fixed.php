<?php

declare(strict_types = 1);

final class StripeEventSanitizer
{
    private function sanitizeChargeDispute(FormProvider $provider, string $stripeEventId, StripeWebhookEventType $eventType, StripeObject $dispute): StripeEventData
    {
        return StripeEventData::fromDispute($provider, $stripeEventId, $eventType, $dispute);
    }
}
