<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * License Object (OAS 3.1 / 3.2)
 *
 * Описывает лицензию, применяемую к API.
 * В 3.1+ добавлено поле "identifier" (SPDX ID).
 */
final class License implements Extensible
{
    use HasExtensions;

    /**
     * @param string              $name       Название лицензии (обязательно)
     * @param string|null         $identifier SPDX идентификатор (3.1+)
     * @param string|null         $url        Ссылка на текст лицензии
     * @param array<string,mixed> $extensions Specification Extensions (только ключи 'x-*')
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $identifier = null,
        public readonly ?string $url = null,
        public readonly array $extensions = [],
    ) {
        if ($this->name === '') {
            throw new \InvalidArgumentException('Поле "name" не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->name,
            $this->identifier,
            $this->url,
            $extensions,
        );
    }
}
