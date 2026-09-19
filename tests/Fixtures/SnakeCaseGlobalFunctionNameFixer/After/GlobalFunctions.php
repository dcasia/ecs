<?php

declare(strict_types = 1);

namespace {
    if (!function_exists('guarded_helper')) {

        function guarded_helper(): string
        {
            return 'guarded';
        }
    }

    guarded_helper();
}

namespace Project\Library {
    function build_payload(): array
    {
        return [];
    }

    function parse_url_value(): string
    {
        return 'value';
    }

    function load_http_response(): string
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
    use function Project\Library\build_payload;
    use function Project\Library\load_http_response as responseLoader;
    use function Project\Library\parse_url_value as parserAlias;

    build_payload();
    parserAlias();
    responseLoader();
    \Project\Library\parse_url_value();

    $builder = build_payload(...);

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
            build_payload();

            $this->buildPayload();

            self::parseURLValue();
        }
    }
}
