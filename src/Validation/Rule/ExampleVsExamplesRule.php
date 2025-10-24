<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;

/**
 * Для Parameter/Header/MediaType: 'example' и 'examples' взаимоисключающиe.
 */
final class ExampleVsExamplesRule implements NodeValidator
{
    /** @var array<string,true> */
    private array $targets = [
        'On1kel\OAS\Core\Model\Parameter' => true,
        'On1kel\OAS\Core\Model\Header'    => true,
        'On1kel\OAS\Core\Model\MediaType' => true,
    ];

    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!isset($this->targets[$node::class])) {
            return [];
        }

        $hasExample  = property_exists($node, 'example')  && $node->example  !== null;
        $hasExamples = property_exists($node, 'examples') && $node->examples !== null;

        if ($hasExample && $hasExamples) {
            return [
                new ValidationError(
                    pointer: $ctx->pointer(),
                    code: 'example.examples.conflict',
                    message: "Fields 'example' and 'examples' are mutually exclusive.",
                    severity: Severity::Error
                ),
            ];
        }

        return [];
    }
}
