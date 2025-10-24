<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Validation\Validator;

final class ValidatorFactory
{
    public function create(SpecProfile $profile): Validator
    {
        // Можно расширить профильно: if ($profile->id()==='3.2') { добавить спец-правила... }
        return new CompositeValidator(DefaultRules::common());
    }
}
