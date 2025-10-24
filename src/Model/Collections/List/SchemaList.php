<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\Reference;
use On1kel\OAS\Core\Model\Schema;

/**
 * Список под-схем: list<Schema|Reference>
 *
 * @extends BaseList<Schema|Reference>
 */
final class SchemaList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Schema || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Schema|Reference';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
