<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\Parameter;

final class PathParameterRequiredRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, Parameter::class)) {
            return [];
        }

        $pointer  = $ctx->pointer();
        $in       = property_exists($node, 'in') ? $node->in : null;
        $required = property_exists($node, 'required') ? $node->required : null;

        // В твоей модели, вероятно, $in — enum/строка. Сравним со строкой.
        if ($in === 'path' && $required !== true) {
            return [
                new ValidationError(
                    pointer: $pointer,
                    code: 'parameter.path.required',
                    message: 'Path parameter MUST have required=true.',
                    severity: Severity::Error
                ),
            ];
        }

        return [];
    }
}
