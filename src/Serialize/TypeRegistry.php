<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Serialize;

use Closure;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use RuntimeException;

/**
 * Реестр фабрик для денормализации: nodeType -> callable(array $data, SpecProfile $profile): object
 * Позволяет гибко подключать конструкторы immutable-моделей без рефлексии и if-ветвлений.
 */
final class TypeRegistry
{
    /** @var array<string, Closure> */
    private array $factories = [];

    /**
     * Зарегистрировать фабрику типа.
     * @param callable(array,SpecProfile):object $factory
     */
    public function register(string $nodeType, callable $factory): void
    {
        $this->factories[$nodeType] = $factory(...);
    }

    public function has(string $nodeType): bool
    {
        return isset($this->factories[$nodeType]);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function make(string $nodeType, array $data, SpecProfile $profile): object
    {
        if (!isset($this->factories[$nodeType])) {
            throw new RuntimeException("No denormalizer factory registered for node type '{$nodeType}'");
        }
        /** @var callable(array,SpecProfile):object $f */
        $f = $this->factories[$nodeType];

        return $f($data, $profile);
    }
}
