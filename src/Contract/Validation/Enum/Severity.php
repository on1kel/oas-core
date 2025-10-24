<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation\Enum;

/**
 * Уровень важности замечания валидатора.
 */
enum Severity: string
{
    case Error   = 'error';
    case Warning = 'warning';
    case Info    = 'info';
}
