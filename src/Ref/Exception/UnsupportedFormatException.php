<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref\Exception;

final class UnsupportedFormatException extends RefException
{
    public static function for(string $source): self
    {
        return new self("Поддерживается только JSON. Сначала преобразуйте YAML в JSON: {$source}");
    }
}
