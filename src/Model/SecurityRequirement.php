<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Security Requirement Object (OAS 3.1 / 3.2)
 *
 * Карта: имя схемы безопасности => список требуемых скоупов (может быть пустым).
 * Пример: { "oauth2": ["read", "write"], "api_key": [] }
 *
 * @see https://spec.openapis.org/oas/v3.1.0#security-requirement-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#security-requirement-object
 */
final class SecurityRequirement implements Extensible
{
    use HasExtensions;

    /**
     * @param array<string,list<string>> $requirements Карта: имя_схемы => список_скоупов (может быть пустым).
     * @param array<string,mixed>        $extensions   x-* расширения.
     */
    public function __construct(
        public readonly array $requirements = [],
        public readonly array $extensions = [],
    ) {
        foreach ($this->requirements as $scheme => $scopes) {
            if (!\is_string($scheme) || $scheme === '') {
                throw new \InvalidArgumentException('SecurityRequirement: имя схемы должно быть непустой строкой.');
            }
            if (!\is_array($scopes) || !\array_is_list($scopes)) {
                throw new \InvalidArgumentException('SecurityRequirement: скоупы должны быть списком строк (list<string>).');
            }
            foreach ($scopes as $scope) {
                if (!\is_string($scope) || $scope === '') {
                    throw new \InvalidArgumentException('SecurityRequirement: каждый скоуп должен быть непустой строкой.');
                }
            }
        }

        self::assertExtensionsKeys($this->extensions);
    }

    /**
     * Есть ли хотя бы одно требование.
     */
    public function hasAny(): bool
    {
        return $this->requirements !== [];
    }

    /**
     * Проверить наличие требования по имени схемы.
     */
    public function has(string $schemeName): bool
    {
        return \array_key_exists($schemeName, $this->requirements);
    }

    /**
     * Получить список скоупов для схемы (пустой список, если схема есть, но скоупов нет).
     *
     * @return list<string>
     */
    public function scopes(string $schemeName): array
    {
        /** @var list<string> */
        $scopes = $this->requirements[$schemeName] ?? [];

        return $scopes;
    }

    /**
     * Список имён схем.
     *
     * @return list<string>
     */
    public function schemeNames(): array
    {
        return \array_keys($this->requirements);
    }

    /**
     * Внутреннее представление.
     *
     * @return array<string,list<string>>
     */
    public function toArray(): array
    {
        return $this->requirements;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self($this->requirements, $extensions);
    }
}
