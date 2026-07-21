<?php

declare(strict_types = 1);

final class SalesValidator
{
    public function validate(int $salesAssociateId): void
    {
        if (!$this->userRepository->exists($salesAssociateId)) {
            throw new RuntimeException();
        }
    }
}
