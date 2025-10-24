<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Server Variable Object (OAS 3.1 / 3.2)
 *
 * Переменная шаблона URL сервера, например {port} или {region}.
 */
final class ServerVariable implements Extensible
{
    use HasExtensions;

    /**
     * @param string              $default     Значение по умолчанию (обязательно)
     * @param string|null         $description Описание переменной
     * @param list<string>        $enum        Допустимые значения (опционально)
     * @param array<string,mixed> $extensions  Specification Extensions (x-*)
     */
    public function __construct(
        public readonly string $default,
        public readonly ?string $description = null,
        public readonly array $enum = [],
        public readonly array $extensions = [],
    ) {
        if ($this->default === '') {
            throw new \InvalidArgumentException('Поле "default" не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasEnum(): bool
    {
        return $this->enum !== [];
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->default,
            $this->description,
            $this->enum,
            $extensions,
        );
    }
}
