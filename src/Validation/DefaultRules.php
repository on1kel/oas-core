<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation;

use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Validation\Rule\ExampleVsExamplesRule;
use On1kel\OAS\Core\Validation\Rule\InfoRequiredFieldsRule;
use On1kel\OAS\Core\Validation\Rule\OperationMustHaveResponsesRule;
use On1kel\OAS\Core\Validation\Rule\PathParameterRequiredRule;
use On1kel\OAS\Core\Validation\Rule\PathsKeyFormatRule;
use On1kel\OAS\Core\Validation\Rule\PathTemplateParametersRule;
use On1kel\OAS\Core\Validation\Rule\ReferenceNoSiblingsRule;
use On1kel\OAS\Core\Validation\Rule\ResponsesStatusCodeRule;
use On1kel\OAS\Core\Validation\Rule\UniqueOperationIdRule;

/**
 * Подбирает здравый минимум правил, общий для 3.1/3.2.
 */
final class DefaultRules
{
    /**
     * @return list<NodeValidator>
     */
    public static function common(): array
    {
        return [
            new InfoRequiredFieldsRule(),
            new UniqueOperationIdRule(),
            new OperationMustHaveResponsesRule(),
            new ResponsesStatusCodeRule(),
            new PathParameterRequiredRule(),
            new PathTemplateParametersRule(),
            new PathsKeyFormatRule(),
            new ExampleVsExamplesRule(),
            new ReferenceNoSiblingsRule(),
        ];
    }
}
