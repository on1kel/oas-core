<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\MediaType;

/**
 * Карта media-type: mime => MediaType.
 *
 * @extends BaseMap<string, MediaType>
 */
final class MediaTypeMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof MediaType;
    }

    protected function typeLabel(): string
    {
        return 'MediaType';
    }
}
