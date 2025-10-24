<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

/**
 * Стиль сериализации параметра/заголовка.
 * См. таблицу в спецификации (matrix, label, form, simple, spaceDelimited, pipeDelimited, deepObject).
 */
enum Style: string
{
    case Matrix         = 'matrix';
    case Label          = 'label';
    case Form           = 'form';
    case Simple         = 'simple';
    case SpaceDelimited = 'spaceDelimited'; // только для query
    case PipeDelimited  = 'pipeDelimited';  // только для query
    case DeepObject     = 'deepObject';     // только для query
}
