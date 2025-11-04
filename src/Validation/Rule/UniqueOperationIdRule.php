<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\Operation;

final class UniqueOperationIdRule implements NodeValidator
{
    /** @var array<string, string> map operationId => pointer */
    private array $seen = [];

    /**
     * @param  string                                           $path
     * @param  object                                           $node
     * @param  ValidationContext                                $ctx
     * @return array<string, ValidationError>|ValidationError[]
     */
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, Operation::class)) {
            return [];
        }

        $opId = $node->operationId ?? null;
        if (!is_string($opId) || $opId === '') {
            return [];
        }

        $ptr = $ctx->pointer();
        if (isset($this->seen[$opId])) {
            return [
                new ValidationError(
                    pointer: $ptr,
                    code: 'operation.operationId.duplicate',
                    message: "operationId '{$opId}' must be unique (already used at {$this->seen[$opId]}).",
                    severity: Severity::Error
                ),
            ];
        }

        $this->seen[$opId] = $ptr;

        return [];
    }
}
