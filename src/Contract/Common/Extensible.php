<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Common;

/**
 * Контракт узла, поддерживающего Specification Extensions (x-*).
 */
interface Extensible
{
    /**
     * Есть ли хотя бы одно расширение (x-*).
     */
    public function hasExtensions(): bool;

    /**
     * Получить расширение по имени (или null, если нет).
     *
     * @return mixed|null
     */
    public function extension(string $name): mixed;

    /**
     * Вернуть новый экземпляр с добавленным/заменённым расширением.
     */
    public function withExtension(string $name, mixed $value): static;

    /**
     * Вернуть новый экземпляр с объединённым набором расширений.
     *
     * @param array<string,mixed> $map
     */
    public function withExtensions(array $map): static;

    /**
     * Возвратить все расширения как ассоциативный массив.
     *
     * @return array<string,mixed>
     */
    public function extensions(): array;
}
