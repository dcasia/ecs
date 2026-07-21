<?php

declare(strict_types = 1);

final class SalesValidator
{
    public function validate(?int $salesAssociateId, int $retailerId): void
    {
        if (!$this->userRepository->existsWithRoleAndRetailer(
            userId: $salesAssociateId,
            role: UserRole::RetailerSalesConsultant,
            retailerId: $retailerId,
        ) && $salesAssociateId !== null) {

            throw new RuntimeException();

        }
    }
}
