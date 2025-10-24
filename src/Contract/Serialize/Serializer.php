<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Serialize;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;

/**
 * Высокоуровневый контракт сериализации / десериализации OpenAPI документа.
 *
 * Содержит операции для:
 *  - конверсии типизированной модели в массив или JSON;
 *  - обратной сборки модели из массива/JSON;
 *  - учёта профиля (3.1, 3.2 и т.д.).
 */
interface Serializer
{
    /**
     * Преобразовать модель (или корень документа) в массив, соответствующий профилю.
     *
     * @param object      $object  Корневая модель (OpenApiDocument или другой узел)
     * @param SpecProfile $profile Активный профиль спецификации
     *
     * @return array<string,mixed> Ассоциативное представление (готовое для JSON/YAML)
     */
    public function toArray(object $object, SpecProfile $profile): array;

    /**
     * Преобразовать модель (или корень) в JSON-строку.
     *
     * @param object      $object  Корневая модель
     * @param SpecProfile $profile Активный профиль спецификации
     * @param int         $flags   Флаги json_encode (по умолчанию JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
     *
     * @return string JSON-представление документа
     */
    public function toJson(object $object, SpecProfile $profile, int $flags = 320): string;

    /**
     * Преобразовать массив данных в модель.
     *
     * @param array<string,mixed> $data    Ассоциативный массив (результат json_decode или YAML-парсера)
     * @param SpecProfile         $profile Активный профиль спецификации
     *
     * @return object Типизированная модель (например, OpenApiDocument)
     */
    public function fromArray(array $data, SpecProfile $profile): object;

    /**
     * Преобразовать JSON-строку в модель.
     *
     * @param string      $json    Исходный JSON
     * @param SpecProfile $profile Активный профиль спецификации
     *
     * @return object Типизированная модель
     */
    public function fromJson(string $json, SpecProfile $profile): object;
}
