<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer\Library {

    function assemble(string $key, array $value): array
    {
        return [ $key => $value ];
    }

    final class Payload
    {
        public static function make(string $key, array $value): array
        {
            return [ $key => $value ];
        }
    }
}

namespace DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer\Application {

    use function DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer\Library\{assemble as assemble_payload};

    use DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer\Library\{Payload as ImportedPayload};

    class BaseHandler
    {
        protected function handle(string $event, array $payload): void
        {
        }
    }

    final class Handler extends BaseHandler
    {
        public function run(array $payload): void
        {
            ImportedPayload::make(
                'imported',
                $payload,
            );

            assemble_payload(
                'aliased',
                $payload,
            );

            \DigitalCreative\ECS\Tests\Fixtures\MultilineNamedArgumentsFixer\Library\assemble(
                'qualified',
                $payload,
            );

            self::record(
                'self',
                $payload,
            );

            $this->handle(
                'inherited',
                $payload,
            );

            parent::handle(
                'parent',
                $payload,
            );
        }

        private static function record(string $event, array $payload): void
        {
        }
    }
}
