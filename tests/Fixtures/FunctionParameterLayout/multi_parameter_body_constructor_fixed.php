<?php

declare(strict_types = 1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class StripeChargeDisputePayloadData extends StripeEventPayloadData
{
    public readonly string $schemaVersion;

    public function __construct(
        public readonly string $disputeId,
        public readonly string $chargeId,
        public readonly ?string $paymentIntentId,
        public readonly ?int $amountDisputedMinor,
        public readonly ?string $currency,
        public readonly ?string $reason,
        public readonly ?string $status,
        public readonly ?int $evidenceDueBy,
        public readonly ?bool $hasEvidence,
        public readonly ?bool $evidencePastDue,
        public readonly ?int $evidenceSubmissionCount,
    )
    {
        $this->schemaVersion = 'v1';
    }
}
