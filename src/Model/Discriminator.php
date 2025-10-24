<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Discriminator Object (OAS 3.1 / 3.2)
 *
 * @see https://spec.openapis.org/oas/v3.1.0#discriminator-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#discriminator-object
 *
 * Поля:
 *  - propertyName (string, REQUIRED)
 *  - mapping (object<string,string>), optional
 *  - defaultMapping (string), optional — OAS 3.2+
 *  - x-* extensions
 */
final class Discriminator implements Extensible
{
    use HasExtensions;

    /**
     * @param string                    $propertyName   Обязательное имя свойства-дискриминатора.
     * @param array<string,string>|null $mapping        Карта «значение → $ref/имя схемы».
     * @param string|null               $defaultMapping (3.2) Ссылка/имя схемы по умолчанию.
     * @param array<string,mixed>       $extensions     Расширения (x-*).
     */
    public function __construct(
        public readonly string $propertyName,
        public readonly ?array $mapping = null,
        public readonly ?string $defaultMapping = null,
        public readonly array $extensions = [],
    ) {
        if ($this->propertyName === '') {
            throw new \InvalidArgumentException('Discriminator: поле "propertyName" обязательно и не может быть пустым.');
        }

        if ($this->mapping !== null) {
            if (!\is_array($this->mapping)) {
                throw new \InvalidArgumentException('Discriminator: "mapping" должен быть массивом string => string.');
            }
            foreach ($this->mapping as $discValue => $target) {
                if (!\is_string($discValue) || $discValue === '') {
                    throw new \InvalidArgumentException('Discriminator: ключи "mapping" должны быть непустыми строками.');
                }
                if (!\is_string($target) || $target === '') {
                    throw new \InvalidArgumentException('Discriminator: значения "mapping" должны быть непустыми строками.');
                }
            }
        }

        if ($this->defaultMapping === '') {
            throw new \InvalidArgumentException('Discriminator: "defaultMapping", если указан, не может быть пустым.');
        }

        self::assertExtensionsKeys($this->extensions);
    }

    public function hasMapping(): bool
    {
        return $this->mapping !== null && $this->mapping !== [];
    }

    public function has(string $discValue): bool
    {
        return $this->mapping !== null && \array_key_exists($discValue, $this->mapping);
    }

    public function target(string $discValue): ?string
    {
        return $this->mapping[$discValue] ?? $this->defaultMapping;
    }

    /** @return array<string,string> */
    public function all(): array
    {
        return $this->mapping ?? [];
    }

    public function hasDefaultMapping(): bool
    {
        return $this->defaultMapping !== null && $this->defaultMapping !== '';
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->propertyName,
            $this->mapping,
            $this->defaultMapping,
            $extensions,
        );
    }
}
