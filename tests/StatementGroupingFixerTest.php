<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests;

use DigitalCreative\ECS\Tests\Support\EcsTestCase;

final class StatementGroupingFixerTest extends EcsTestCase
{
    public function test_groups_variables_static_calls_and_this_calls_separately(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/StatementGroupingFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/OrderWorkflow.php',
            $fixtureDirectory . '/After/OrderWorkflow.php',
        );
    }

    public function test_groups_static_calls_and_matching_variable_receivers(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/StatementGroupingFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/CampaignWorkflow.php',
            $fixtureDirectory . '/After/CampaignWorkflow.php',
        );
    }

    public function test_preserves_multiline_spacing_inside_matching_groups(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/StatementGroupingFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/MultilineWorkflow.php',
            $fixtureDirectory . '/After/MultilineWorkflow.php',
        );
    }

    public function test_groups_destructuring_assignments_and_properties_by_type(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/StatementGroupingFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/AssignmentAndProperties.php',
            $fixtureDirectory . '/After/AssignmentAndProperties.php',
        );
    }

    public function test_only_adds_group_separators_without_removing_existing_lines(): void
    {
        $fixtureDirectory = __DIR__ . '/Fixtures/StatementGroupingFixer';

        $this->assertFixtureIsFixedTo(
            $fixtureDirectory . '/Before/AdditiveGrouping.php',
            $fixtureDirectory . '/After/AdditiveGrouping.php',
        );
    }
}
