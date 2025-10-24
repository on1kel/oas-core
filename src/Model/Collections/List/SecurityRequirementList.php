<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\SecurityRequirement;

/**
 * Список требований безопасности на уровне операции/документа.
 * Каждый элемент описывает одно требование (schemeName => scopes[]).
 *
 * @extends BaseList<SecurityRequirement>
 */
final class SecurityRequirementList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof SecurityRequirement;
    }

    protected function typeLabel(): string
    {
        return 'SecurityRequirement';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
