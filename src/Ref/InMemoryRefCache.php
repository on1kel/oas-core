<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref;

use On1kel\OAS\Core\Contract\Ref\RefCache;
use On1kel\OAS\Core\Contract\Ref\RefResolution;

final class InMemoryRefCache implements RefCache
{
    /** @var array<string, RefResolution> */
    private array $store = [];

    public function get(string $cacheKey): ?RefResolution
    {
        return $this->store[$cacheKey] ?? null;
    }

    public function put(string $cacheKey, RefResolution $resolution): void
    {
        $this->store[$cacheKey] = $resolution;
    }

    public function forget(string $cacheKey): void
    {
        unset($this->store[$cacheKey]);
    }

    public function clear(): void
    {
        $this->store = [];
    }
}
