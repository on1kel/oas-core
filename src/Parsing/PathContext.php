<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Parsing;

final class PathContext
{
    /** @var list<string> */
    private array $segments;
    public function __construct(
        public readonly string $baseUri,
        array $segments = [],
    ) {
        $this->segments = array_values($segments);
    }

    public static function root(string $baseUri): self
    {
        return new self($baseUri, []);
    }

    public function pointer(): string
    {
        if ($this->segments === []) {
            return '';
        }
        $escape = static fn (string $s) => str_replace(['~', '/'], ['~0', '~1'], $s);

        return '/' . implode('/', array_map($escape, $this->segments));
    }

    public function push(string|int $segment): self
    {
        $next = $this->segments;
        $next[] = (string)$segment;

        return new self($this->baseUri, $next);
    }

    public function withBaseUri(string $baseUri): self
    {
        return new self($baseUri, $this->segments);
    }
}
