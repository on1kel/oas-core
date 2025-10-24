<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Parameter;
use On1kel\OAS\Core\Model\Reference;

/**
 * @extends BaseMap<string, Parameter|Reference>
 */
final class ParameterMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Parameter || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Parameter|Reference';
    }
}
