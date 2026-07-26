<?php

declare(strict_types = 1);

final class ApiClient
{
    public function request(string $method, string $absUrl, array $headers, array $params, bool $hasFile, string $apiMode = 'v1', ?int $maxNetworkRetries = null): void
    {
    }
}
