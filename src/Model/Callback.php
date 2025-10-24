<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\CallbackMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Callback Object (OAS 3.1 / 3.2)
 *
 * Представляет карту асинхронных вызовов, выполняемых сервером
 * при наступлении определённого события.
 *
 * @see https://spec.openapis.org/oas/v3.1.0#callback-object
 * @see https://spec.openapis.org/oas/v3.2.0#callback-object
 */
final class Callback implements Extensible
{
    use HasExtensions;

    /**
     * @param \On1kel\OAS\Model\Collections\Map\CallbackMap|null $expressionMap Карта runtime-выражений → PathItem|Reference.
     * @param array<string,mixed>                                $extensions    Расширения (x-*).
     */
    public function __construct(
        public readonly ?CallbackMap $expressionMap = null,
        public readonly array $extensions = [],
    ) {
        $hasExpressions = $this->expressionMap !== null && $this->expressionMap->count() > 0;
        $hasExtensions  = $this->extensions !== [];

        if (!$hasExpressions && !$hasExtensions) {
            throw new \InvalidArgumentException(
                'Callback: должен содержать хотя бы одно выражение или расширение x-*.'
            );
        }

        self::assertExtensionsKeys($this->extensions);
    }

    public function hasExpressions(): bool
    {
        return $this->expressionMap !== null && $this->expressionMap->count() > 0;
    }

    public function hasExpression(string $expression): bool
    {
        return $this->expressionMap?->has($expression) ?? false;
    }

    public function getExpression(string $expression): PathItem|Reference|null
    {
        /** @var PathItem|Reference|null */
        return $this->expressionMap?->get($expression);
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self($this->expressionMap, $extensions);
    }
}
