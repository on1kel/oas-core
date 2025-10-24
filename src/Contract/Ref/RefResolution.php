<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Ref;

/**
 * DTO результата разрешения ссылки $ref.
 *
 * Описывает, во что превратилась исходная ссылка (какой документ и какой фрагмент),
 * и содержит распарсенные данные целевого узла.
 */
final class RefResolution
{
    /**
     * @param string               $originalRef Исходное значение $ref, как встретилось в документе (может быть относительным)
     * @param string|null          $baseUri     Базовый URI контекста, внутри которого была найдена ссылка (может быть null для корня)
     * @param string               $resolvedUri Абсолютный URI документа, в котором находится целевой фрагмент
     * @param string               $pointer     JSON Pointer до целевого узла (начинается с '#' или '#/')
     * @param array<string, mixed> $data        Распарсенные данные целевого узла (ассоциативный массив)
     */
    public function __construct(
        public readonly string  $originalRef,
        public readonly ?string $baseUri,
        public readonly string  $resolvedUri,
        public readonly string  $pointer,
        public readonly array   $data,
    ) {
    }

    /**
     * Рекомендованный ключ для кеширования результата.
     * Формат: "{resolvedBase}|{originalRef}", где resolvedBase = $baseUri или $resolvedUri,
     * в зависимости от потребностей резолвера.
     *
     * Используйте этот метод, если нет собственной стратегии ключей.
     */
    public function cacheKey(): string
    {
        $base = $this->baseUri ?? $this->resolvedUri;

        return $base . '|' . $this->originalRef;
    }
}
