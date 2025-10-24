<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\MediaTypeMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Request Body Object (OAS 3.1 / 3.2)
 *
 * Описывает тело запроса: обязательная карта content (media type -> MediaType),
 * опциональное описание и флаг required.
 * 3.1: https://spec.openapis.org/oas/v3.1.0#request-body-object
 * 3.2: https://spec.openapis.org/oas/v3.2.0#request-body-object
 */
final class RequestBody implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null $description
     * @param MediaTypeMap $content Обязательная карта mime-типов -> MediaType (НЕ может быть пустой).
     * @param bool $required
     * @param array<string,mixed> $extensions
     */
    public function __construct(
        public readonly ?string $description,
        public readonly MediaTypeMap $content,
        public readonly bool $required = false,
        public readonly array $extensions = [],
    ) {
        if ($this->content->count() === 0) {
            throw new \InvalidArgumentException('RequestBody: поле "content" обязательно и не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    public function hasMediaType(string $mime): bool
    {
        return $this->content->has($mime);
    }

    public function mediaType(string $mime): ?MediaType
    {
        /** @var MediaType|null $mt */
        $mt = $this->content->get($mime);

        return $mt;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->description,
            $this->content,
            $this->required,
            $extensions,
        );
    }
}
