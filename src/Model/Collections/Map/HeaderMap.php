<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Header;
use On1kel\OAS\Core\Model\Reference;

/**
 * Карта заголовков: name => (Header|Reference)
 *
 * @extends BaseMap<string, Header|Reference>
 */
final class HeaderMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Header || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Header|Reference';
    }
}
