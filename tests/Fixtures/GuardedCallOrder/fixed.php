<?php

declare(strict_types = 1);

$condition = $salesAssociateId !== null && !$this->userRepository->existsWithRoleAndRetailer(
    userId: $salesAssociateId,
    role: UserRole::RetailerSalesConsultant,
    retailerId: $retailerId,
);
