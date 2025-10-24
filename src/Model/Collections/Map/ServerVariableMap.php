<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\ServerVariable;

/**
 * Карта переменных сервера: name => ServerVariable
 */
final class ServerVariableMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof ServerVariable;
    }

    protected function typeLabel(): string
    {
        return 'ServerVariable';
    }
}
