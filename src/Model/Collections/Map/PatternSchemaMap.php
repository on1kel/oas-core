<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Reference;
use On1kel\OAS\Core\Model\Schema;

/**
 * Карта шаблонных свойств: /pattern/ => (Schema|Reference)
 *
 * Ключ — строка-шаблон регулярного выражения.
 *
 * @extends BaseMap<string, Schema|Reference>
 */
final class PatternSchemaMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Schema || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Schema|Reference';
    }
}
