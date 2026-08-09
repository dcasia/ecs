<?php

declare(strict_types = 1);

switch ($outcome) {
    case RequestedPaymentOutcome::Recorded:
        $deliveries->markRecorded($delivery);

        break;
    case RequestedPaymentOutcome::Skipped:
        $deliveries->release($delivery);

        break;
}

switch ($outcome):
    case RequestedPaymentOutcome::Recorded:
        $description = 'recorded';

        break;
    default:
        $description = 'skipped';

        break;
endswitch;
