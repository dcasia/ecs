<?php

declare(strict_types = 1);

final class Service
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly InquiryRepository $inquiryRepository,
        private readonly LeadRepository $leadRepository,
        private readonly UserRepository $userRepository,
    )
    {
    }
}
