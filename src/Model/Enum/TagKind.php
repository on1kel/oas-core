<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

/**
 * TagKind (OAS 3.2)
 * @see https://spec.openapis.org/oas/v3.2.0#tag-object
 */
enum TagKind: string
{
    case Group = 'group';
    case Operation = 'operation';
}
