<?php

declare(strict_types = 1);

final class CampaignTestFixture
{
    public function test_retailer_campaigns_require_hq_approval_and_remain_retailer_scoped(): void
    {
        $this->assertFalse($marketing->can('update', $otherCampaign));
        Livewire::actingAs($marketing)
            ->test(CreateCampaign::class)
            ->set('data', [
                'name' => 'Retailer Approval Campaign',
                'status' => CampaignStatus::Published->value,
                'visibility' => CampaignVisibility::Guest->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
        $createdCampaign = app(CampaignRepository::class)->soleByName('Retailer Approval Campaign');
    }
}
