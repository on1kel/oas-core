<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\Collections\Map\ResponseMap;
use On1kel\OAS\Core\Model\Operation;

final class ResponsesStatusCodeRule implements NodeValidator
{
    /**
     * @param  string                $path
     * @param  object                $node
     * @param  ValidationContext     $ctx
     * @return list<ValidationError>
     */
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        // Правило применимо только к Operation
        if (!$node instanceof Operation) {
            return [];
        }

        $errors = [];
        $ptr = $ctx->pointer();

        $responsesObject = $node->responses;

        $map = $responsesObject->responses;
        if (!$map instanceof ResponseMap) {
            return $errors;
        }

        foreach ($map->all() as $code => $_resp) {
            $codeStr = (string) $code;

            if ($codeStr === 'default') {
                continue;
            }

            if (!preg_match('/^(?:[1-5][0-9]{2})$/', $codeStr)) {
                $errors[] = new ValidationError(
                    pointer: $ptr,
                    code: 'responses.status.invalid',
                    message: "Invalid response status code key '{$codeStr}'. "
                    . "Expected 'default' or 3-digit HTTP code 100-599.",
                    severity: Severity::Error
                );
            }
        }

        return $errors;
    }
}
