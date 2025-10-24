<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\PathItem;
use On1kel\OAS\Core\Model\Reference;

/**
 * @extends BaseMap<string, PathItem|Reference>
 *
 * Карта путей: "/pets" => PathItem|Reference
 */
final class PathItemMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof PathItem || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'PathItem|Reference';
    }
}
