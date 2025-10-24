<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\ResponseMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Responses Object (OAS 3.1 / 3.2)
 *
 * Контейнер для всех возможных ответов операции.
 * Содержит фиксированное поле `default` и карту других ответов,
 * где ключами являются HTTP-коды (например "200", "404", "2XX").
 *
 * @see https://spec.openapis.org/oas/v3.1.0#responses-object
 * @see https://spec.openapis.org/oas/v3.2.0#responses-object
 */
final class Responses implements Extensible
{
    use HasExtensions;

    /**
     * @param Response|Reference|null $default    Ответ по умолчанию (для неуказанных кодов).
     * @param ResponseMap|null        $responses  Карта HTTP-кодов → Response|Reference.
     * @param array<string,mixed>     $extensions Расширения (x-*).
     */
    public function __construct(
        public readonly Response|Reference|null $default = null,
        public readonly ?ResponseMap $responses = null,
        public readonly array $extensions = [],
    ) {
        $hasDefault   = $this->default !== null;
        $hasResponses = $this->responses !== null && $this->responses->count() > 0;

        if (!$hasDefault && !$hasResponses) {
            throw new \InvalidArgumentException(
                'Responses: объект должен содержать хотя бы одно из полей: "default" или конкретный HTTP-код.'
            );
        }

        self::assertExtensionsKeys($this->extensions);
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function has(string $code): bool
    {
        return $this->responses?->has($code) ?? false;
    }

    public function get(string $code): Response|Reference|null
    {
        /** @var Response|Reference|null */
        return $this->responses?->get($code);
    }

    public function hasAny(): bool
    {
        return ($this->responses?->count() ?? 0) > 0;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self($this->default, $this->responses, $extensions);
    }
}
