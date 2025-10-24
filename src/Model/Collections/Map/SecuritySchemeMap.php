<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Reference;
use On1kel\OAS\Core\Model\SecurityScheme;

/**
 * @extends BaseMap<string, SecurityScheme|Reference>
 */
final class SecuritySchemeMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof  SecurityScheme || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return ' SecurityScheme|Reference';
    }
}
