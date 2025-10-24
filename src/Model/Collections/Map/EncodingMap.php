<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\Encoding;
use On1kel\OAS\Core\Model\Reference;

/**
 * Карта кодировок: имя поля → Encoding|Reference
 *
 * Применяется в MediaType для описания специфичных параметров кодирования.
 *
 * @see https://spec.openapis.org/oas/v3.1.0#encoding-object
 * @see https://spec.openapis.org/oas/v3.2.0#encoding-object
 *
 * @extends BaseMap<string, Encoding|Reference>
 */
final class EncodingMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Encoding || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Encoding|Reference';
    }
}
