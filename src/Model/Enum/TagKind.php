<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

/**
 * TagKind (OAS 3.2)
 * @see https://spec.openapis.org/oas/v3.2.0#tag-object
 * @see https://spec.openapis.org/registry/tag-kind/
 */
enum TagKind: string
{
    /** Указывает целевую аудиторию для операции. */
    case Audience = 'audience';

    /** Применяется в качестве видимых значков в документации. */
    case Badge = 'badge';

    /** Используется в документации для группировки операций по разделам */
    case Nav = 'nav';
}
