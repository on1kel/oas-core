<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Profile;

use On1kel\OAS\Core\Contract\Validation\NodeValidator;

/**
 * Контракт профиля спецификации (например, OAS 3.1 или OAS 3.2).
 * Профиль описывает различия между версиями: разрешённые/обязательные ключи,
 * нормализацию ключей, фичи и дополнительные валидаторные правила.
 */
interface SpecProfile
{
    /**
     * Возвращает идентификатор версии в формате "MAJOR.MINOR" (например, "3.1", "3.2").
     */
    public function majorMinor(): string;

    /**
     * Список разрешённых ключей для конкретного типа узла модели.
     * Используется парсером/сериализатором для фильтрации полей.
     *
     * @return array<int, string>
     */
    public function allowedKeysFor(string $nodeType): array;

    /**
     * Список ОБЯЗАТЕЛЬНЫХ ключей для данного типа узла в рамках текущего профиля.
     * Используется валидаторами профиля (и/или билдерами шагов).
     *
     * @return array<int, string>
     */
    public function requiredKeysFor(string $nodeType): array;

    /**
     * Нормализация ключа для совместимости между версиями (алиасы/переименования).
     * Должна возвращать финальное имя ключа, которое будет использоваться в модели.
     */
    public function normalizeKey(string $nodeType, string $key): string;

    /**
     * Набор фич-флагов для условного поведения парсера/сериализатора/билдеров/линтов.
     */
    public function features(): FeatureSet;

    /**
     * Дополнительные валидаторы узлов, специфичные для версии.
     *
     * @return array<int, NodeValidator>
     */
    public function extraValidators(): array;
}
