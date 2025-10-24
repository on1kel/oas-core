<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

use On1kel\OAS\Core\Model\Server;

/**
 * @extends BaseList<Server>
 */
final class ServerList extends BaseList
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof Server;
    }

    protected function typeLabel(): string
    {
        return 'Server';
    }

    public function hasAny(): bool
    {
        return $this->items !== [];
    }
}
