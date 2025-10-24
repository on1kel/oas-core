<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\Info;
use Severity;

final class InfoRequiredFieldsRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, Info::class)) {
            return [];
        }

        $ptr = $ctx->pointer();
        $out = [];

        $title = property_exists($node, 'title') ? $node->title : null;
        if (!is_string($title) || $title === '') {
            $out[] = new ValidationError(
                pointer: $ptr,
                code: 'info.title.required',
                message: 'Info.title is required and must be a non-empty string.',
                severity: Severity::Error
            );
        }

        $version = property_exists($node, 'version') ? $node->version : null;
        if (!is_string($version) || $version === '') {
            $out[] = new ValidationError(
                pointer: $ptr,
                code: 'info.version.required',
                message: 'Info.version is required and must be a non-empty string.',
                severity: Severity::Error
            );
        }

        return $out;
    }
}
