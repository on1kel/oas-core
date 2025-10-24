<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Ref;

/**
 * Контракт загрузчика внешних документов для разрешения ссылок $ref.
 *
 * Реализация должна уметь принимать абсолютные и относительные URI.
 * Примеры:
 *  - "file:///path/to/openapi.yaml"
 *  - "/abs/path/openapi.json" (относительно текущего процесса — НЕ рекомендуется)
 *  - "./common.yaml" + $baseUri → нормализовать к абсолютному
 *  - "https://example.com/openapi.yaml"
 *
 * Возвращает ассоциативный массив — уже распарсенные данные документа (JSON/YAML).
 * Парсинг формата (JSON/YAML) — зона ответственности конкретной реализации.
 */
interface RefFetcher
{
    /**
     * Загрузить и распарсить документ по URI.
     *
     * @param string      $uri     Абсолютный или относительный (к $baseUri) URI документа
     * @param string|null $baseUri Базовый URI, если $uri относительный (может быть пустым для абсолютных)
     *
     *
     * @throws \RuntimeException   Если документ не удалось загрузить/распарсить
     * @return array<string,mixed> Распарсенный документ (корень), пригодный для дальнейшего JSON Pointer-доступа
     */
    public function fetch(string $uri, ?string $baseUri = null): array;
}
