<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Version\Exception;

use RuntimeException;

final class ProfileNotFound extends RuntimeException
{
    public static function byId(string $id): self
    {
        return new self("Специальный профиль не найден: '{$id}'");
    }
}
