<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Profile\Enum;

/**
 * Режим строгости обработки несовместимых/лишних ключей между версиями.
 */
enum Strictness: string
{
    /**
     * Строгий режим: несовместимые для профиля поля считаются ошибкой.
     */
    case Strict = 'strict';

    /**
     * Мягкий режим: несовместимые поля игнорируются или понижаются до предупреждений.
     */
    case Lenient = 'lenient';
}
