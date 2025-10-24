<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * OAuth Flow Object (OAS 3.1 / 3.2)
 *
 * 3.2 добавляет:
 * - deviceAuthorizationUrl (для deviceAuthorization flow)
 * - расширяет применимость tokenUrl
 *
 * @see https://spec.openapis.org/oas/v3.2.0.html#oauth-flow-object
 */
final class OAuthFlow implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null          $authorizationUrl       REQUIRED для implicit/authorizationCode.
     * @param string|null          $tokenUrl               REQUIRED для password/clientCredentials/authorizationCode/deviceAuthorization.
     * @param string|null          $refreshUrl             Опционально.
     * @param array<string,string> $scopes                 REQUIRED (карта scope => описание). Может быть пустой.
     * @param string|null          $deviceAuthorizationUrl REQUIRED для deviceAuthorization (3.2).
     * @param array<string,mixed>  $extensions             x-*.
     */
    public function __construct(
        public readonly ?string $authorizationUrl = null,
        public readonly ?string $tokenUrl = null,
        public readonly ?string $refreshUrl = null,
        public readonly array $scopes = [],
        public readonly ?string $deviceAuthorizationUrl = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);

        // scopes: карта строк
        foreach ($this->scopes as $name => $desc) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException('OAuthFlow: ключи scopes должны быть строками.');
            }
            if (!\is_string($desc)) {
                throw new \InvalidArgumentException('OAuthFlow: значения scopes должны быть строками.');
            }
        }
    }

    public function hasAuthorizationUrl(): bool
    {
        return $this->authorizationUrl !== null && $this->authorizationUrl !== '';
    }
    public function hasTokenUrl(): bool
    {
        return $this->tokenUrl !== null && $this->tokenUrl !== '';
    }
    public function hasRefreshUrl(): bool
    {
        return $this->refreshUrl !== null && $this->refreshUrl !== '';
    }
    public function hasDeviceAuthorizationUrl(): bool
    {
        return $this->deviceAuthorizationUrl !== null && $this->deviceAuthorizationUrl !== '';
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->authorizationUrl,
            $this->tokenUrl,
            $this->refreshUrl,
            $this->scopes,
            $this->deviceAuthorizationUrl,
            $extensions,
        );
    }
}
