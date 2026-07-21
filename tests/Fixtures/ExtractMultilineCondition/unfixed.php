<?php

declare(strict_types = 1);

final class SalesValidator
{
    public function validate(?int $salesAssociateId, int $retailerId): void
    {
        if ($salesAssociateId !== null && !$this->userRepository->existsWithRoleAndRetailer(
            userId: $salesAssociateId,
            role: UserRole::RetailerSalesConsultant,
            retailerId: $retailerId,
        )) {

            throw ValidationException::withMessages(messages: [
                'sales_associate_id' => __('The sales consultant must belong to the selected retailer.'),
            ]);

        }
    }
}
