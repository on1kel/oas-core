<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;

/**
 * Ключи в Paths должны начинаться с '/' (например, '/pets').
 * Проверяем на PathItem, извлекая сам ключ из JSON Pointer.
 */
final class PathsKeyFormatRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, 'On1kel\OAS\Core\Model\PathItem')) {
            return [];
        }

        $ptr = $ctx->pointer();
        $pathStr = $this->extractPathFromPointer($ptr);
        if ($pathStr === null) {
            return [];
        }

        if ($pathStr === '' || $pathStr[0] !== '/') {
            return [
                new ValidationError(
                    pointer: $ptr,
                    code: 'paths.key.invalid',
                    message: "Path template key must start with '/'. Found: '{$pathStr}'",
                    severity: Severity::Error
                ),
            ];
        }

        return [];
    }

    private function extractPathFromPointer(string $pointer): ?string
    {
        if (!str_starts_with($pointer, '#/paths/')) {
            return null;
        }
        $seg = substr($pointer, strlen('#/paths/'));
        $firstSlash = strpos($seg, '/');
        $p = $firstSlash === false ? $seg : substr($seg, 0, $firstSlash);

        return str_replace(['~1','~0'], ['/','~'], $p) ?: null;
    }
}
