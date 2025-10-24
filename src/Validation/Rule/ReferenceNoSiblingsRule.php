<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;

/**
 * OAS Reference Object: при наличии $ref, никакие другие поля, кроме x-*, недопустимы.
 * Исключение: Schema — это JSON Schema, там сиблинги позволены (3.1).
 */
final class ReferenceNoSiblingsRule implements NodeValidator
{
    /** @var array<string,true> */
    private array $referenceable = [
        'On1kel\OAS\Core\Model\Header'          => true,
        'On1kel\OAS\Core\Model\Parameter'       => true,
        'On1kel\OAS\Core\Model\Response'        => true,
        'On1kel\OAS\Core\Model\Example'         => true,
        'On1kel\OAS\Core\Model\RequestBody'     => true,
        'On1kel\OAS\Core\Model\Link'            => true,
        'On1kel\OAS\Core\Model\Callback'        => true,
        'On1kel\OAS\Core\Model\SecurityScheme'  => true,
        'On1kel\OAS\Core\Model\PathItem'        => true,
    ];

    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        $cls = $node::class;
        if (!isset($this->referenceable[$cls])) {
            return [];
        }

        /** @var array<string,mixed> $props */
        $props = get_object_vars($node);
        if (!array_key_exists('$ref', $props)) {
            return [];
        }
        if (!is_string($props['$ref']) || $props['$ref'] === '') {
            return [
                new ValidationError(
                    pointer: $ctx->pointer(),
                    code: 'ref.format.invalid',
                    message: "Reference object must have non-empty string in '\$ref'.",
                    severity: Severity::Error
                ),
            ];
        }

        // всё, что не $ref и не x-*, запрещаем
        $bad = [];
        foreach ($props as $k => $_) {
            if ($k === '$ref') {
                continue;
            }
            if (str_starts_with($k, 'x-')) {
                continue;
            }
            $bad[] = $k;
        }

        if ($bad === []) {
            return [];
        }

        return [
            new ValidationError(
                pointer: $ctx->pointer(),
                code: 'ref.has.siblings',
                message: 'Reference object with $ref MUST NOT have sibling fields (except x-*): ' . implode(', ', $bad),
                severity: Severity::Error
            ),
        ];
    }
}
