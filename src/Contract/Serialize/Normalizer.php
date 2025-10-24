<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Serialize;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;

/**
 * Преобразует объект модели в структурированный массив (array representation).
 *
 * Normalizer работает на уровне отдельных объектов модели (Schema, Info, Operation и т.д.).
 * Это низкоуровневый компонент: не занимается JSON-кодированием и форматированием.
 */
interface Normalizer
{
    /**
     * Проверяет, поддерживает ли нормализатор данный объект.
     *
     * @param object $object Экземпляр модели
     */
    public function supports(object $object): bool;

    /**
     * Нормализует объект в ассоциативный массив.
     *
     * @param object      $object  Экземпляр модели
     * @param SpecProfile $profile Активный профиль спецификации
     *
     * @return array<string, mixed> Структурированные данные узла
     */
    public function normalize(object $object, SpecProfile $profile): array;
}
