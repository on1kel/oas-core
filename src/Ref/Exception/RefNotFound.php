<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref\Exception;

final class RefNotFound extends RefException
{
    public static function for(string $ref, string $baseUri): self
    {
        return new self("Целевая ссылка не найдена: '{$ref}' (базовый URI: {$baseUri})");
    }
}
