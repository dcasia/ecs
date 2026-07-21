<?php

declare(strict_types = 1);

final class SingleLineAssignmentFixture
{
    public function createUsers(): void
    {
        $managerA = $this->createStaffUser(UserRole::RetailerSalesManager, $retailerA->id);
        $consultantA = $this->createStaffUser(UserRole::RetailerSalesConsultant, $retailerA->id);
        $otherConsultantA = $this->createStaffUser(UserRole::RetailerSalesConsultant, $retailerA->id);
    }
}
