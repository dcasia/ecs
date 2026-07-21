<?php

declare(strict_types = 1);

final class VisibilityService
{
    private function visibility(
        FormDefinitionData $definition,
        FormAnswersData $answers,
        FormCalculationResultsData $calculations,
    ): FormVisibilityData {
        return new FormVisibilityData();
    }
}
