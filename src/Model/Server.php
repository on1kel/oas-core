<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\ServerVariableMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Server Object (OAS 3.1 / 3.2)
 *
 * Описывает базовый сервер API.
 * URL может содержать шаблонные переменные: https://{region}.example.com:{port}/v1
 */
final class Server implements Extensible
{
    use HasExtensions;

    /**
     * @param string                 $url         Базовый URL сервера (обязателен)
     * @param string|null            $description Описание сервера
     * @param ServerVariableMap|null $variables   Карта переменных (name -> ServerVariable)
     * @param array<string,mixed>    $extensions  Specification Extensions (x-*)
     */
    public function __construct(
        public readonly string $url,
        public readonly ?string $description = null,
        public readonly ?ServerVariableMap $variables = null,
        public readonly array $extensions = [],
    ) {
        if ($this->url === '') {
            throw new \InvalidArgumentException('Поле "url" не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    /**
     * Проверка наличия переменной по имени.
     */
    public function hasVariable(string $name): bool
    {
        return $this->variables?->has($name) ?? false;
    }

    /**
     * Получить переменную по имени.
     */
    public function variable(string $name): ?ServerVariable
    {
        return $this->variables?->get($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->url,
            $this->description,
            $this->variables,
            $extensions,
        );
    }
}
