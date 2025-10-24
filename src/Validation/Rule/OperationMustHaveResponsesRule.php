<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;

final class OperationMustHaveResponsesRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, 'On1kel\OAS\Core\Model\Operation')) {
            return [];
        }

        $responses = property_exists($node, 'responses') ? $node->responses : null;
        $has = false;

        if ($responses instanceof \Traversable) {
            foreach ($responses as $_k => $_v) {
                $has = true;
                break;
            }
        } elseif (is_array($responses)) {
            $has = !empty($responses);
        }

        if ($has) {
            return [];
        }

        $sev = $ctx->strictness()->value === 'Lenient' ? Severity::Warning : Severity::Error;

        return [
            new ValidationError(
                pointer: $ctx->pointer(),
                code: 'operation.responses.missing',
                message: 'Operation MUST declare at least one response.',
                severity: $sev
            ),
        ];
    }
}
