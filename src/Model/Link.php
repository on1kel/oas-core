<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Link Object (OAS 3.1 / 3.2)
 *
 * @see https://spec.openapis.org/oas/v3.1.0#link-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#link-object
 */
final class Link implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null              $operationRef Взаимоисключается с $operationId. URI-ссылка на Operation.
     * @param string|null              $operationId  Взаимоисключается с $operationRef. Имя существующей операции.
     * @param array<string,mixed>|null $parameters   Карта: имя параметра (можно квалифицировать, напр. "path.id") → константа или runtime-выражение.
     * @param mixed|null               $requestBody  Константа или runtime-выражение для тела запроса целевой операции.
     * @param string|null              $description  Описание (CommonMark).
     * @param Server|null              $server       Переопределение сервера для целевой операции.
     * @param array<string,mixed>      $extensions   x-* расширения.
     */
    public function __construct(
        public readonly ?string $operationRef = null,
        public readonly ?string $operationId = null,
        public readonly ?array $parameters = null,
        public readonly mixed $requestBody = null,
        public readonly ?string $description = null,
        public readonly ?Server $server = null,
        public readonly array $extensions = [],
    ) {
        // x-* ключи
        self::assertExtensionsKeys($this->extensions);

        // Взаимоисключение operationRef / operationId
        if ($this->operationRef !== null && $this->operationId !== null) {
            throw new \InvalidArgumentException('Link: "operationRef" и "operationId" взаимоисключают друг друга.');
        }
        // Должен быть указан хотя бы один из operationRef / operationId
        if ($this->operationRef === null && $this->operationId === null) {
            throw new \InvalidArgumentException('Link: должен быть указан "operationRef" или "operationId".');
        }

        // Валидация parameters-как-карты
        if ($this->parameters !== null) {
            if (!\is_array($this->parameters)) {
                throw new \InvalidArgumentException('Link: "parameters" должен быть картой string => mixed.');
            }
            foreach ($this->parameters as $name => $_) {
                if (!\is_string($name) || $name === '') {
                    throw new \InvalidArgumentException('Link: ключи "parameters" должны быть непустыми строками.');
                }
            }
        }
    }

    public function hasParameters(): bool
    {
        return $this->parameters !== null && $this->parameters !== [];
    }

    public function hasRequestBody(): bool
    {
        return $this->requestBody !== null;
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    public function hasServer(): bool
    {
        return $this->server !== null;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->operationRef,
            $this->operationId,
            $this->parameters,
            $this->requestBody,
            $this->description,
            $this->server,
            $extensions,
        );
    }
}
