<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Reference;
use On1kel\OAS\Core\Model\Response;

/**
 * Карта кодов ответа (Responses Object)
 * @extends BaseMap<string, Response|Reference>
 */
final class ResponseMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Response || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Response|Reference';
    }

    public function hasAny(): bool
    {
        return $this->count() > 0;
    }
}
