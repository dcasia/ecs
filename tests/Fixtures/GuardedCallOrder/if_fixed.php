<?php

declare(strict_types = 1);

final class SalesValidator
{
    public function validate(?int $salesAssociateId, int $retailerId): void
    {
        $condition = $salesAssociateId !== null && !$this->userRepository->existsWithRoleAndRetailer(
            userId: $salesAssociateId,
            role: UserRole::RetailerSalesConsultant,
            retailerId: $retailerId,
        );

        if ($condition) {
            throw new RuntimeException();
        }
    }
}
