<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * External Documentation Object (OAS 3.1 / 3.2)
 *
 * Содержит дополнительную документацию для элемента спецификации.
 * https://spec.openapis.org/oas/v3.1.0#external-documentation-object
 */
final class ExternalDocumentation implements Extensible
{
    use HasExtensions;

    /**
     * @param string              $url         Абсолютный или относительный URL (обязателен)
     * @param string|null         $description Текст описания
     * @param array<string,mixed> $extensions  Specification Extensions (x-*)
     */
    public function __construct(
        public readonly string $url,
        public readonly ?string $description = null,
        public readonly array $extensions = [],
    ) {
        if ($this->url === '') {
            throw new \InvalidArgumentException('Поле "url" не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    /** {@inheritDoc} */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self($this->url, $this->description, $extensions);
    }
}
