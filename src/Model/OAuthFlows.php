<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * OAuth Flows Object (OAS 3.1 / 3.2)
 *
 * 3.2 добавляет deviceAuthorization.
 *
 * @see https://spec.openapis.org/oas/v3.2.0.html#oauth-flows-object
 */
final class OAuthFlows implements Extensible
{
    use HasExtensions;

    /**
     * @param OAuthFlow|null      $implicit
     * @param OAuthFlow|null      $password
     * @param OAuthFlow|null      $clientCredentials
     * @param OAuthFlow|null      $authorizationCode
     * @param OAuthFlow|null      $deviceAuthorization (3.2)
     * @param array<string,mixed> $extensions
     */
    public function __construct(
        public readonly ?OAuthFlow $implicit = null,
        public readonly ?OAuthFlow $password = null,
        public readonly ?OAuthFlow $clientCredentials = null,
        public readonly ?OAuthFlow $authorizationCode = null,
        public readonly ?OAuthFlow $deviceAuthorization = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasAny(): bool
    {
        return $this->implicit !== null
            || $this->password !== null
            || $this->clientCredentials !== null
            || $this->authorizationCode !== null
            || $this->deviceAuthorization !== null;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->implicit,
            $this->password,
            $this->clientCredentials,
            $this->authorizationCode,
            $this->deviceAuthorization,
            $extensions,
        );
    }
}
