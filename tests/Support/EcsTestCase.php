<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Support;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class EcsTestCase extends TestCase
{
    private const string PROJECT_ROOT = __DIR__ . '/../..';

    final protected function assertFixtureIsFixedTo(string $inputFixture, string $expectedFixture): void
    {
        $result = $this->runEcs($this->readFixture($inputFixture), true);

        self::assertSame(0, $result[ 'exitCode' ], $result[ 'output' ]);
        self::assertSame($this->readFixture($expectedFixture), $result[ 'source' ]);
    }

    final protected function assertFixtureIsFixedToUsingConfig(
        string $inputFixture,
        string $expectedFixture,
        string $config,
    ): void
    {
        $result = $this->runEcs($this->readFixture($inputFixture), true, $config);

        self::assertSame(0, $result[ 'exitCode' ], $result[ 'output' ]);
        self::assertSame($this->readFixture($expectedFixture), $result[ 'source' ]);
    }

    final protected function assertFixturePasses(string $fixture): void
    {
        $source = $this->readFixture($fixture);
        $result = $this->runEcs($source, false);

        self::assertSame(0, $result[ 'exitCode' ], $result[ 'output' ]);
        self::assertSame($source, $result[ 'source' ]);
    }

    final protected function assertFixturePassesUsingConfig(string $fixture, string $config): void
    {
        $source = $this->readFixture($fixture);

        $result = $this->runEcs(
            source: $source,
            fix: false,
            config: $config,
            filename: basename($fixture),
        );

        self::assertSame(0, $result[ 'exitCode' ], $result[ 'output' ]);
        self::assertSame($source, $result[ 'source' ]);
    }

    final protected function assertFixtureFailsWithUsingConfig(
        string $fixture,
        array $messages,
        string $config,
    ): void
    {

        $result = $this->runEcs(
            source: $this->readFixture($fixture),
            fix: false,
            config: $config,
            filename: basename($fixture),
        );

        self::assertSame(2, $result[ 'exitCode' ], $result[ 'output' ]);

        foreach ($messages as $message) {
            self::assertStringContainsString($message, $result[ 'output' ]);
        }
    }

    final protected function assertFixtureFailsWith(string $fixture, array $messages): void
    {
        $result = $this->runEcs($this->readFixture($fixture), false);

        self::assertSame(2, $result[ 'exitCode' ], $result[ 'output' ]);

        foreach ($messages as $message) {
            self::assertStringContainsString($message, $result[ 'output' ]);
        }
    }

    private function readFixture(string $fixture): string
    {
        $source = file_get_contents($fixture);

        if ($source === false) {
            throw new RuntimeException("Unable to read ECS fixture: {$fixture}");
        }

        return $source;
    }

    /**
     * @return array{exitCode: int, output: string, source: string}
     */
    private function runEcs(
        string $source,
        bool $fix,
        string $config = self::PROJECT_ROOT . '/src/Custom.php',
        ?string $filename = null,
    ): array
    {

        $path = sys_get_temp_dir()
        . '/digital-creative-ecs-'
        . bin2hex(random_bytes(8))
        . '-'
        . ($filename ?? 'fixture.php');

        if (file_put_contents($path, $source) === false) {
            throw new RuntimeException("Unable to write ECS fixture: {$path}");
        }

        $command = [
            PHP_BINARY,
            self::PROJECT_ROOT . '/vendor/bin/ecs',
            'check',
            $path,
            '--config',
            $config,
            '--no-progress-bar',
        ];

        if ($fix) {
            $command[] = '--fix';
        }

        $pipes = [];

        $process = proc_open(
            command: $command,
            descriptor_spec: [
                1 => [ 'pipe', 'w' ],
                2 => [ 'pipe', 'w' ],
            ],
            pipes: $pipes,
            cwd: self::PROJECT_ROOT,
        );

        if (!is_resource($process)) {

            unlink($path);

            throw new RuntimeException('Unable to start ECS.');

        }

        try {

            $output = stream_get_contents($pipes[ 1 ]) . stream_get_contents($pipes[ 2 ]);
            fclose($pipes[ 1 ]);
            fclose($pipes[ 2 ]);

            $exitCode = proc_close($process);

            return [
                'exitCode' => $exitCode,
                'output' => $output,
                'source' => (string) file_get_contents($path),
            ];

        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
            }

            unlink($path);
        }
    }
}
