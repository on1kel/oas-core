<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Contact Object (OAS 3.1 / 3.2)
 *
 * Контактная информация владельца/поддержки API.
 * Все поля опциональны и неизменяемы; x-* расширения поддерживаются через Extensible.
 */
final class Contact implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null         $name       Имя контактного лица/организации
     * @param string|null         $url        URL страницы контакта (URI-строка)
     * @param string|null         $email      Email для связи
     * @param array<string,mixed> $extensions Specification Extensions (только ключи 'x-*')
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $url = null,
        public readonly ?string $email = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->name,
            $this->url,
            $this->email,
            $extensions,
        );
    }
}
