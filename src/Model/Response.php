<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\HeaderMap;
use On1kel\OAS\Core\Model\Collections\Map\LinkMap;
use On1kel\OAS\Core\Model\Collections\Map\MediaTypeMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Response Object (OAS 3.1 / 3.2)
 *
 * Описывает один HTTP-ответ.
 * @see https://spec.openapis.org/oas/v3.1.0#response-object
 * @see https://spec.openapis.org/oas/v3.2.0#response-object
 */
final class Response implements Extensible
{
    use HasExtensions;

    /**
     * @param string              $description Обязательное описание.
     * @param HeaderMap|null      $headers     Заголовки.
     * @param MediaTypeMap|null   $content     Тело ответа по MIME-типам.
     * @param LinkMap|null        $links       Ссылки на операции, доступные из ответа.
     * @param array<string,mixed> $extensions  Расширения (x-*).
     */
    public function __construct(
        public readonly string $description,
        public readonly ?HeaderMap $headers = null,
        public readonly ?MediaTypeMap $content = null,
        public readonly ?LinkMap $links = null,
        public readonly array $extensions = [],
    ) {
        if ($this->description === '') {
            throw new \InvalidArgumentException('Response: "description" обязателен и не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasHeaders(): bool
    {
        return $this->headers !== null && $this->headers->count() > 0;
    }

    public function hasContent(): bool
    {
        return $this->content !== null && $this->content->count() > 0;
    }

    public function hasLinks(): bool
    {
        return $this->links !== null && $this->links->count() > 0;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->description,
            $this->headers,
            $this->content,
            $this->links,
            $extensions,
        );
    }
}
