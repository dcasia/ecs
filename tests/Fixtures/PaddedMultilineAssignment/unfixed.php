<?php

declare(strict_types = 1);

final class MultilineAssignmentFixture
{
    public function createLeads(): void
    {
        $managerA = $this->createStaffUser(UserRole::RetailerSalesManager, $retailerA->id);
        $consultantA = $this->createStaffUser(UserRole::RetailerSalesConsultant, $retailerA->id);
        $otherConsultantA = $this->createStaffUser(UserRole::RetailerSalesConsultant, $retailerA->id);
        $assigned = LeadFactory::new()->create([
            'name' => 'Assigned',
            'retailer_id' => $retailerA->id,
            'sales_associate_id' => $consultantA->id,
        ]);
        $shared = LeadFactory::new()->create([
            'name' => 'Shared',
            'retailer_id' => $retailerA->id,
            'sales_team_visible' => true,
        ]);
        $this->assertNotSame($assigned, $shared);
    }

    public function createOneLead(): void
    {
        $assigned = LeadFactory::new()->create([
            'name' => 'Assigned',
            'retailer_id' => $retailerA->id,
        ]);
    }
}
