<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Support;

trait HasExtensions
{
    public function hasExtensions(): bool
    {
        /** @var array<string,mixed> $extensions */
        $extensions = $this->extensions ?? [];

        return $extensions !== [];
    }

    public function extension(string $name): mixed
    {
        /** @var array<string,mixed> $extensions */
        $extensions = $this->extensions ?? [];

        return $extensions[$name] ?? null;
    }

    public function withExtension(string $name, mixed $value): static
    {
        if (!\str_starts_with($name, 'x-')) {
            throw new \InvalidArgumentException("Имя расширения должно начинаться с 'x-': {$name}");
        }
        /** @var array<string,mixed> $extensions */
        $extensions = $this->extensions ?? [];
        $extensions[$name] = $value;

        return $this->recreateWithExtensions($extensions);
    }

    public function withExtensions(array $map): static
    {
        foreach ($map as $k => $_) {
            if (!\is_string($k) || !\str_starts_with($k, 'x-')) {
                throw new \InvalidArgumentException("Ключ расширения должен начинаться с 'x-': {$k}");
            }
        }
        /** @var array<string,mixed> $extensions */
        $extensions = $this->extensions ?? [];
        // приоритет новых значений из $map
        $merged = $extensions === [] ? $map : [...$extensions, ...$map];

        return $this->recreateWithExtensions($merged);
    }

    /**
     * @return array<string,mixed>
     */
    public function extensions(): array
    {
        /** @var array<string,mixed> $extensions */
        $extensions = $this->extensions ?? [];

        return $extensions;
    }

    /**
     * Должен вернуть новый экземпляр текущего класса с заменённым массивом $extensions.
     * Реализуется в самом классе (через new self(...)).
     *
     * @param array<string,mixed> $extensions
     */
    abstract protected function recreateWithExtensions(array $extensions): static;

    /**
     * Утилита для конструкторов: проверить корректность ключей расширений.
     *
     * @param array<string,mixed> $extensions
     */
    protected static function assertExtensionsKeys(array $extensions): void
    {
        foreach ($extensions as $k => $_) {
            if (!\is_string($k) || !\str_starts_with($k, 'x-')) {
                throw new \InvalidArgumentException("Ключ расширения должен начинаться с 'x-': {$k}");
            }
        }
    }
}
