<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Support;

use RuntimeException;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractCheckerTestCase;

abstract class EcsTestCase extends AbstractCheckerTestCase
{
    private FixerFileProcessor $fixerFileProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixerFileProcessor = $this->make(FixerFileProcessor::class);
    }

    final public function provideConfig(): string
    {
        return dirname(__DIR__, 2) . '/src/Custom.php';
    }

    final protected function assertFixtureIsFixedTo(string $inputFixture, string $expectedFixture): void
    {
        $this->assertCodeIsFixedTo(
            $this->readFixture($inputFixture),
            $this->readFixture($expectedFixture),
            basename($inputFixture),
        );
    }

    final protected function assertFixturePasses(string $fixture): void
    {
        $source = $this->readFixture($fixture);

        $this->assertCodeIsFixedTo($source, $source, basename($fixture));
    }

    final protected function assertCodeIsFixedTo(
        string $input,
        string $expected,
        string $temporaryFilename = 'fixture.php',
    ): void
    {
        self::assertNotEmpty($this->fixerFileProcessor->getCheckers(), 'The ECS configuration registered no fixers.');

        $temporaryDirectory = sprintf(
            '%s/digital-creative-ecs-tests/%s',
            sys_get_temp_dir(),
            bin2hex(random_bytes(16)),
        );

        if (mkdir($temporaryDirectory, recursive: true) === false && is_dir($temporaryDirectory) === false) {
            throw new RuntimeException(sprintf('Unable to create temporary directory "%s".', $temporaryDirectory));
        }

        $temporaryFile = sprintf('%s/%s', $temporaryDirectory, $temporaryFilename);

        if (file_put_contents($temporaryFile, $input) === false) {
            throw new RuntimeException(sprintf('Unable to write temporary fixture "%s".', $temporaryFile));
        }

        try {
            self::assertSame($expected, $this->fixerFileProcessor->processFileToString($temporaryFile));
        } finally {
            unlink($temporaryFile);
            rmdir($temporaryDirectory);
        }
    }

    private function readFixture(string $fixture): string
    {
        $contents = file_get_contents($fixture);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read fixture "%s".', $fixture));
        }

        return $contents;
    }
}
