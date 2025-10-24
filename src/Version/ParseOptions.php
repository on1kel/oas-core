<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Version;

use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;

/**
 * Опции парсинга/резолвинга: режим строгости, глубина $ref и т.д.
 */
final class ParseOptions
{
    public function __construct(
        public readonly Strictness $strictness = Strictness::Strict,
        public readonly bool $resolveExternalRefs = true,
        public readonly int $maxRefDepth = 64,
    ) {
    }

    public static function strict(
        bool $resolveExternalRefs = true,
        int $maxRefDepth = 64,
    ): self {
        return new self(Strictness::Strict, $resolveExternalRefs, $maxRefDepth);
    }

    public static function lenient(
        bool $resolveExternalRefs = true,
        int $maxRefDepth = 64,
    ): self {
        return new self(Strictness::Lenient, $resolveExternalRefs, $maxRefDepth);
    }

    public function withStrictness(Strictness $strictness): self
    {
        return new self($strictness, $this->resolveExternalRefs, $this->maxRefDepth);
    }

    public function withResolveExternalRefs(bool $resolve): self
    {
        return new self($this->strictness, $resolve, $this->maxRefDepth);
    }

    public function withMaxRefDepth(int $depth): self
    {
        return new self($this->strictness, $this->resolveExternalRefs, $depth);
    }
}
