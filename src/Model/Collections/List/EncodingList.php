<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\Encoding;

/**
 * Список Encoding для prefixEncoding (OAS 3.2).
 *
 * @extends BaseList<Encoding>
 */
final class EncodingList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Encoding;
    }

    protected function typeLabel(): string
    {
        return 'Encoding';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
