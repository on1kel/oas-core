<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Reference;

/**
 * Карта кодов ответа (RequestBodyMap Object)
 * @extends BaseMap<string, RequestBodyMap|Reference>
 */
final class RequestBodyMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof RequestBodyMap || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'RequestBodyMap|Reference';
    }

    public function hasAny(): bool
    {
        return $this->count() > 0;
    }
}
