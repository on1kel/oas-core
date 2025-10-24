<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Ref;

/**
 * Контракт кеша результатов разрешения ссылок $ref.
 *
 * Ключ кеша рекомендуется формировать как "{baseUri}|{ref}" для уникальности
 * в пространстве документа. Например:
 *   "https://host/api.yaml|#/components/schemas/User"
 *   "file:///cwd/openapi.yaml|./common.yaml#/defs/Foo"
 */
interface RefCache
{
    /**
     * Получить значение по ключу кеша.
     *
     * @param string $cacheKey Произвольный строковый ключ (обычно "{baseUri}|{ref}")
     *
     * @return RefResolution|null Найденная резолюция или null, если нет в кеше
     */
    public function get(string $cacheKey): ?RefResolution;

    /**
     * Сохранить значение по ключу кеша.
     *
     * @param string        $cacheKey   Ключ кеша
     * @param RefResolution $resolution Результат разрешения ссылки
     */
    public function put(string $cacheKey, RefResolution $resolution): void;

    /**
     * Удалить одно значение из кеша.
     *
     * @param string $cacheKey Ключ кеша
     */
    public function forget(string $cacheKey): void;

    /**
     * Полностью очистить кеш.
     */
    public function clear(): void;
}
