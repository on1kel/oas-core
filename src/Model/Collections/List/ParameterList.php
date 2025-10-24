<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\Parameter;
use On1kel\OAS\Core\Model\Reference;

/**
 * @extends BaseList<Parameter|Reference>
 *
 * Список параметров для PathItem/Operation.
 */
final class ParameterList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Parameter || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'Parameter|Reference';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
