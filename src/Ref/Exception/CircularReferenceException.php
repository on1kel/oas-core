<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref\Exception;

final class CircularReferenceException extends RefException
{
    /**
     * @param list<string> $chain
     */
    public static function detected(array $chain, string $next): self
    {
        $chainStr = implode(' -> ', $chain);

        return new self("Обнаружена циклическая \$ref-ссылка: {$chainStr} -> {$next}");
    }
}
