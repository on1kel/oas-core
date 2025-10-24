<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\Tag;

/**
 * Список тегов операции/документа.
 *
 * @extends BaseList<Tag>
 */
final class TagList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Tag;
    }

    protected function typeLabel(): string
    {
        return 'Tag';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
