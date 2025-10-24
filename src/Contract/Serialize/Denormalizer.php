<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Serialize;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;

/**
 * Обратная операция к Normalizer — создаёт объект модели из массива данных.
 *
 * Используется парсером для материализации структуры OAS в типизированные модели.
 */
interface Denormalizer
{
    /**
     * Проверяет, поддерживает ли данный тип узла (например, "Schema", "Info", "Response" и т.д.).
     *
     * @param string $nodeType Имя узла/класса
     */
    public function supports(string $nodeType): bool;

    /**
     * Преобразует массив данных в конкретный объект модели.
     *
     * @param array<string,mixed> $data     Данные узла
     * @param string              $nodeType Имя узла/класса модели
     * @param SpecProfile         $profile  Активный профиль спецификации
     *
     * @return object Инстанс модели (например, On1kel\OAS\Model\Schema)
     */
    public function denormalize(array $data, string $nodeType, SpecProfile $profile): object;
}
