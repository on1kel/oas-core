<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref\Exception;

final class UnsupportedSchemeException extends RefException
{
    public static function for(string $uri): self
    {
        return new self("Неподдерживаемая схема URI в '{$uri}'");
    }
}
