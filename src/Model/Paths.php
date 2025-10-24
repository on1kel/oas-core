<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\PathItemMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Paths Object (OAS 3.1 / 3.2)
 * Карта путей: route => (PathItem|Reference).
 */
final class Paths implements Extensible
{
    use HasExtensions;

    /**
     * @param PathItemMap $items Карта путей
     * @param array<string,mixed> $extensions Specification Extensions (x-*)
     */
    public function __construct(
        public readonly PathItemMap $items,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
    }

    public function has(string $route): bool
    {
        return $this->items->has($route);
    }

    public function get(string $route): PathItem|Reference|null
    {
        return $this->items->get($route);
    }

    /**
     * @return \Traversable<string, PathItem|Reference>
     */
    public function all(): \Traversable
    {
        return $this->items->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self($this->items, $extensions);
    }
}
