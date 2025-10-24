<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

/**
 * Reference Object (OAS 3.1 / 3.2)
 *
 * @see https://spec.openapis.org/oas/v3.1.0#reference-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#reference-object
 *
 * Фиксированные поля:
 * - $ref (string, REQUIRED)
 * - summary (string|null)
 * - description (string|null, CommonMark)
 *
 * ВАЖНО: Дополнительные свойства (включая x-*) не допускаются и должны игнорироваться
 * согласно спецификации. Поэтому класс НЕ реализует Extensible и НЕ содержит extensions.
 */
final class Reference
{
    public function __construct(
        public readonly string $ref,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
    ) {
        if ($this->ref === '') {
            throw new \InvalidArgumentException('Reference: поле "$ref" обязательно и не может быть пустым.');
        }
        // Здесь можно добавить лёгкую синтаксическую проверку URI при желании,
        // но строгая валидация RFC3986 обычно перекладывается на уровень профиля/валидатора.
    }

    public function hasSummary(): bool
    {
        return $this->summary !== null && $this->summary !== '';
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }
}
