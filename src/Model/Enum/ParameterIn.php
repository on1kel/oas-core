<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

enum ParameterIn: string
{
    case Query  = 'query';
    case Header = 'header';
    case Path   = 'path';
    case Cookie = 'cookie';
}
