<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

/**
 * Security Scheme type (OAS 3.1/3.2)
 */
enum SecuritySchemeType: string
{
    case ApiKey        = 'apiKey';
    case Http          = 'http';
    case MutualTLS     = 'mutualTLS';
    case OAuth2        = 'oauth2';
    case OpenIdConnect = 'openIdConnect';
}
