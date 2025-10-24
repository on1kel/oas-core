<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\EncodingList;
use On1kel\OAS\Core\Model\Collections\Map\ExampleMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

final class MediaType implements Extensible
{
    use HasExtensions;

    public function __construct(
        public readonly Schema|Reference|null $schema = null,
        public readonly Schema|Reference|null $itemSchema = null, // OAS 3.2
        public readonly mixed $example = null,
        public readonly ?ExampleMap $examples = null,
        public readonly ?EncodingList $prefixEncoding = null,     // OAS 3.2
        public readonly ?Encoding $itemEncoding = null,           // OAS 3.2
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
        if ($this->example !== null && $this->examples !== null) {
            throw new \InvalidArgumentException('MediaType: "example" и "examples" взаимоисключают друг друга.');
        }
    }

    public function hasPrefixEncoding(): bool
    {
        return $this->prefixEncoding !== null;
    }
    public function hasItemEncoding(): bool
    {
        return $this->itemEncoding !== null;
    }

    /** {@inheritDoc} */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->schema,
            $this->itemSchema,
            $this->example,
            $this->examples,
            $this->prefixEncoding,
            $this->itemEncoding,
            $extensions,
        );
    }
}
