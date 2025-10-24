<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Example;
use On1kel\OAS\Core\Model\Reference;

/**
 * Карта примеров: name => (Example|Reference).
 *
 * @extends BaseMap<string, Example|Reference>
 */
final class ExampleMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Example || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Example|Reference';
    }
}
