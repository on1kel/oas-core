<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation;

use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;

/**
 * Высокоуровневый контракт валидатора документа.
 *
 * Реализация оркестрирует обход дерева, вызывает NodeValidator-правила,
 * агрегирует результаты и возвращает ValidationReport.
 */
interface Validator
{
    /**
     * Валидировать корневой объект (обычно OpenApiDocument) с учётом профиля и режима строгости.
     */
    public function validate(object $root, SpecProfile $profile, Strictness $strictness = Strictness::Strict, ?string $baseUri = null): ValidationReport;
}
