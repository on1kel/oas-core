<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Enum\ParameterIn;
use On1kel\OAS\Core\Model\Enum\SecuritySchemeType;
use On1kel\OAS\Core\Model\Support\HasExtensions; // уже есть в проекте

/**
 * Security Scheme Object (OAS 3.1 / 3.2)
 *
 * Новое в 3.2:
 *  - oauth2MetadataUrl (для discovery по RFC 8414)
 *  - deprecated: bool
 *  - поддержка OAuth2 Device Authorization flow (через OAuthFlows)
 *
 * @see https://spec.openapis.org/oas/v3.2.0.html#security-scheme-object
 */
final class SecurityScheme implements Extensible
{
    use HasExtensions;

    /**
     * @param SecuritySchemeType  $type
     * @param string|null         $description
     * @param string|null         $name              REQUIRED для apiKey.
     * @param ParameterIn|null    $in                REQUIRED для apiKey (только query/header/cookie).
     * @param string|null         $scheme            REQUIRED для http (например: basic, bearer).
     * @param string|null         $bearerFormat      Подсказка для http bearer.
     * @param OAuthFlows|null     $flows             REQUIRED для oauth2.
     * @param string|null         $openIdConnectUrl  REQUIRED для openIdConnect.
     * @param string|null         $oauth2MetadataUrl (3.2) RFC 8414 metadata URL для oauth2.
     * @param bool                $deprecated        (3.2) пометка схемы как устаревшей.
     * @param array<string,mixed> $extensions        x-*.
     */
    public function __construct(
        public readonly SecuritySchemeType $type,
        public readonly ?string $description = null,
        public readonly ?string $name = null,
        public readonly ?ParameterIn $in = null,
        public readonly ?string $scheme = null,
        public readonly ?string $bearerFormat = null,
        public readonly ?OAuthFlows $flows = null,
        public readonly ?string $openIdConnectUrl = null,
        public readonly ?string $oauth2MetadataUrl = null,
        public readonly bool $deprecated = false,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);

        // Валидации по типам (строго по спекам 3.1/3.2).
        switch ($this->type) {
            case SecuritySchemeType::ApiKey:
                if ($this->name === null || $this->name === '') {
                    throw new \InvalidArgumentException('SecurityScheme(apiKey): "name" обязателен.');
                }
                if ($this->in === null) {
                    throw new \InvalidArgumentException('SecurityScheme(apiKey): "in" обязателен.');
                }
                // Разрешены только query/header/cookie
                if (!\in_array($this->in, [ParameterIn::Query, ParameterIn::Header, ParameterIn::Cookie], true)) {
                    throw new \InvalidArgumentException('SecurityScheme(apiKey): "in" должен быть query|header|cookie.');
                }
                // У apiKey запретить http-поля/oidc-поля:
                if ($this->scheme !== null || $this->bearerFormat !== null || $this->flows !== null || $this->openIdConnectUrl !== null) {
                    throw new \InvalidArgumentException('SecurityScheme(apiKey): не должно быть scheme/bearerFormat/flows/openIdConnectUrl.');
                }
                break;

            case SecuritySchemeType::Http:
                if ($this->scheme === null || $this->scheme === '') {
                    throw new \InvalidArgumentException('SecurityScheme(http): "scheme" обязателен.');
                }
                // Для http запретить apiKey-поля/oidc/flows:
                if ($this->name !== null || $this->in !== null || $this->flows !== null || $this->openIdConnectUrl !== null) {
                    throw new \InvalidArgumentException('SecurityScheme(http): не должно быть name/in/flows/openIdConnectUrl.');
                }
                break;

            case SecuritySchemeType::MutualTLS:
                // Никаких специфических обязательных полей.
                if ($this->name !== null || $this->in !== null || $this->scheme !== null || $this->bearerFormat !== null || $this->flows !== null || $this->openIdConnectUrl !== null) {
                    throw new \InvalidArgumentException('SecurityScheme(mutualTLS): не должно быть name/in/scheme/bearerFormat/flows/openIdConnectUrl.');
                }
                break;

            case SecuritySchemeType::OAuth2:
                if ($this->flows === null || !$this->flows->hasAny()) {
                    throw new \InvalidArgumentException('SecurityScheme(oauth2): "flows" обязателен и должен содержать хотя бы один flow.');
                }
                if ($this->name !== null || $this->in !== null || $this->scheme !== null || $this->bearerFormat !== null || $this->openIdConnectUrl !== null) {
                    throw new \InvalidArgumentException('SecurityScheme(oauth2): не должно быть name/in/scheme/bearerFormat/openIdConnectUrl.');
                }
                // oauth2MetadataUrl — новое поле 3.2, допускается (не обязательно).
                break;

            case SecuritySchemeType::OpenIdConnect:
                if ($this->openIdConnectUrl === null || $this->openIdConnectUrl === '') {
                    throw new \InvalidArgumentException('SecurityScheme(openIdConnect): "openIdConnectUrl" обязателен.');
                }
                if ($this->name !== null || $this->in !== null || $this->scheme !== null || $this->bearerFormat !== null || $this->flows !== null) {
                    throw new \InvalidArgumentException('SecurityScheme(openIdConnect): не должно быть name/in/scheme/bearerFormat/flows.');
                }
                if ($this->oauth2MetadataUrl !== null) {
                    // Для openIdConnect поле oauth2MetadataUrl не применяется.
                    throw new \InvalidArgumentException('SecurityScheme(openIdConnect): "oauth2MetadataUrl" неприменим.');
                }
                break;
        }
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->type,
            $this->description,
            $this->name,
            $this->in,
            $this->scheme,
            $this->bearerFormat,
            $this->flows,
            $this->openIdConnectUrl,
            $this->oauth2MetadataUrl,
            $this->deprecated,
            $extensions,
        );
    }
}
