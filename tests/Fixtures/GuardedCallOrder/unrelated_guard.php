<?php

declare(strict_types = 1);

$condition = !$this->userRepository->existsWithRoleAndRetailer(
    userId: $salesAssociateId,
    role: UserRole::RetailerSalesConsultant,
    retailerId: $retailerId,
) && $campaignId !== null;
