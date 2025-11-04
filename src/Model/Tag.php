<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Enum\TagKind;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Tag Object (OAS 3.1 / 3.2)
 *
 * 3.1: name, description, externalDocs.
 * 3.2: добавлены summary, parent, kind.
 *
 * @see https://spec.openapis.org/oas/v3.1.0#tag-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#tag-object
 */
final class Tag implements Extensible
{
    use HasExtensions;

    /**
     * @param string                     $name         REQUIRED (3.1/3.2).
     * @param string|null                $summary      (3.2) Короткий заголовок/лейбл тега.
     * @param string|null                $description  CommonMark.
     * @param ExternalDocumentation|null $externalDocs Доп. документация.
     * @param string|null                $parent       (3.2) Имя родительского тега (должен существовать в описании).
     * @param TagKind|null               $kind         (3.2) Машиночитаемая категория тега (например: "nav", "badge", "audience").
     * @param array<string,mixed>        $extensions   x-* расширения.
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?ExternalDocumentation $externalDocs = null,
        public readonly ?string $parent = null,
        public readonly ?TagKind $kind = null,
        public readonly array $extensions = [],
    ) {
        if ($this->name === '') {
            throw new \InvalidArgumentException('Tag: поле "name" обязательно и не может быть пустым.');
        }
        if ($this->parent === '') {
            throw new \InvalidArgumentException('Tag: поле "parent", если указано, не может быть пустым.');
        }
        if ($this->kind === '') {
            throw new \InvalidArgumentException('Tag: поле "kind", если указано, не может быть пустым.');
        }

        self::assertExtensionsKeys($this->extensions);
        // Проверки существования parent и отсутствия циклов — ответственность уровня валидаторов профиля.
    }

    public function hasSummary(): bool
    {
        return $this->summary !== null && $this->summary !== '';
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    public function hasExternalDocs(): bool
    {
        return $this->externalDocs !== null;
    }

    public function hasParent(): bool
    {
        return $this->parent !== null && $this->parent !== '';
    }

    public function hasKind(): bool
    {
        return $this->kind !== null && $this->kind !== '';
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->name,
            $this->summary,
            $this->description,
            $this->externalDocs,
            $this->parent,
            $this->kind,
            $extensions,
        );
    }
}
