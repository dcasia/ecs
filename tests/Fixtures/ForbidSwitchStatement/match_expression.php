<?php

declare(strict_types = 1);

$result = match ($outcome) {
    RequestedPaymentOutcome::Recorded => $deliveries->markRecorded($delivery),
    RequestedPaymentOutcome::Skipped => $deliveries->release($delivery),
};
