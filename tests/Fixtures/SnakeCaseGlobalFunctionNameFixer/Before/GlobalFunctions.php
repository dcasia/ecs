<?php

declare(strict_types = 1);

namespace {
    if (!function_exists('guardedHelper')) {
        function guardedHelper(): string
        {
            return 'guarded';
        }
    }

    guardedHelper();
}

namespace Project\Library {
    function buildPayload(): array
    {
        return [];
    }

    function parseURLValue(): string
    {
        return 'value';
    }

    function loadHTTPResponse(): string
    {
        return 'response';
    }

    function already_snake(): string
    {
        return 'ready';
    }

    function conflictingName(): string
    {
        return 'camel';
    }

    function conflicting_name(): string
    {
        return 'snake';
    }
}

namespace Project\Consumer {
    use function Project\Library\{buildPayload, loadHTTPResponse as responseLoader};
    use function Project\Library\parseURLValue as parserAlias;

    buildPayload();
    parserAlias();
    responseLoader();
    \Project\Library\parseURLValue();

    $builder = buildPayload(...);

    final class Formatter
    {
        public function buildPayload(): array
        {
            return [];
        }

        public static function parseURLValue(): string
        {
            return 'method';
        }

        public function invoke(): void
        {
            buildPayload();
            $this->buildPayload();
            self::parseURLValue();
        }
    }
}
