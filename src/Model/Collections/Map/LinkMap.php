<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Link;
use On1kel\OAS\Core\Model\Reference;

/**
 * Карта ссылок, доступных из ответа.
 *
 * @extends BaseMap<string, Link|Reference>
 */
final class LinkMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Link || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Link|Reference';
    }
}
