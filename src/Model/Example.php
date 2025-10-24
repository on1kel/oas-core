<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Example Object (OAS 3.1 / 3.2)
 *
 * 3.2 добавляет поля dataValue и serializedValue, уточняя семантику примеров.
 * См. спецификацию:
 * - https://spec.openapis.org/oas/v3.1.0#example-object
 * - https://spec.openapis.org/oas/v3.2.0.html#example-object
 */
final class Example implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null         $summary
     * @param string|null         $description
     * @param mixed|null          $dataValue       (OAS 3.2) Данные в валидационно-готовом виде (соответствуют Schema).
     * @param string|null         $serializedValue (OAS 3.2) Строка сериализованного значения (как «на проводе»).
     * @param string|null         $externalValue   URI на сериализованный пример (файл и т.п.).
     * @param mixed|null          $value           Встроенный пример (3.1/3.2; для 3.2 — использовать с осторожностью).
     * @param array<string,mixed> $extensions      x-* расширения.
     */
    public function __construct(
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly mixed $dataValue = null,
        public readonly ?string $serializedValue = null,
        public readonly ?string $externalValue = null,
        public readonly mixed $value = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);

        // Валидация взаимоисключений по OAS 3.2 (§4.19.1 / §4.19.2):
        // 1) Если присутствует dataValue → value должен отсутствовать.
        if ($this->dataValue !== null && $this->value !== null) {
            throw new \InvalidArgumentException('Example: при наличии "dataValue" поле "value" должно отсутствовать.');
        }

        // 2) Если присутствует serializedValue → value и externalValue должны отсутствовать.
        if ($this->serializedValue !== null) {
            if ($this->value !== null || $this->externalValue !== null) {
                throw new \InvalidArgumentException('Example: при наличии "serializedValue" поля "value" и "externalValue" должны отсутствовать.');
            }
        }

        // 3) Если присутствует externalValue → serializedValue и value должны отсутствовать.
        if ($this->externalValue !== null) {
            if ($this->serializedValue !== null || $this->value !== null) {
                throw new \InvalidArgumentException('Example: при наличии "externalValue" поля "serializedValue" и "value" должны отсутствовать.');
            }
        }

        // Для 3.1 сохраняется историческое правило: value XOR externalValue.
        if ($this->value !== null && $this->externalValue !== null) {
            throw new \InvalidArgumentException('Example: "value" и "externalValue" взаимоисключают друг друга.');
        }
    }

    public function hasSummary(): bool
    {
        return $this->summary !== null && $this->summary !== '';
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    public function hasDataValue(): bool
    {
        return $this->dataValue !== null;
    }

    public function hasSerializedValue(): bool
    {
        return $this->serializedValue !== null && $this->serializedValue !== '';
    }

    public function hasExternalValue(): bool
    {
        return $this->externalValue !== null && $this->externalValue !== '';
    }

    public function hasInlineValue(): bool
    {
        return $this->value !== null;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->summary,
            $this->description,
            $this->dataValue,
            $this->serializedValue,
            $this->externalValue,
            $this->value,
            $extensions,
        );
    }
}
