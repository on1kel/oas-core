<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\CallbackMap;
use On1kel\OAS\Core\Model\Collections\Map\ExampleMap;
use On1kel\OAS\Core\Model\Collections\Map\HeaderMap;
use On1kel\OAS\Core\Model\Collections\Map\LinkMap;
use On1kel\OAS\Core\Model\Collections\Map\MediaTypeMap;
use On1kel\OAS\Core\Model\Collections\Map\ParameterMap;
use On1kel\OAS\Core\Model\Collections\Map\PathItemMap;
use On1kel\OAS\Core\Model\Collections\Map\RequestBodyMap;
use On1kel\OAS\Core\Model\Collections\Map\ResponseMap;
use On1kel\OAS\Core\Model\Collections\Map\SchemaMap;
use On1kel\OAS\Core\Model\Collections\Map\SecuritySchemeMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

// по аналогии подключите остальные карты при реализации:
// ExampleMap, RequestBodyMap, HeaderMap, SecuritySchemeMap, LinkMap, CallbackMap, PathItemMap

/**
 * Components Object (OAS 3.1 / 3.2)
 *
 * Именованные переиспользуемые компоненты спецификации.
 * Каждый раздел — карта name => (InlineObject | Reference).
 */
final class Components implements Extensible
{
    use HasExtensions;

    public function __construct(
        public readonly ?SchemaMap         $schemas = null,
        public readonly ?ResponseMap       $responses = null,
        public readonly ?ParameterMap      $parameters = null,
        public readonly ?ExampleMap        $examples = null,
        public readonly ?RequestBodyMap    $requestBodies = null,
        public readonly ?HeaderMap         $headers = null,
        public readonly ?SecuritySchemeMap $securitySchemes = null,
        public readonly ?LinkMap           $links = null,
        public readonly ?CallbackMap       $callbacks = null,
        public readonly ?PathItemMap       $pathItems = null, // OAS 3.1+
        public readonly ?MediaTypeMap      $mediaTypes = null, // OAS 3.1+
        /** @var array<string,mixed> */
        public readonly array              $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasSchema(string $name): bool
    {
        return $this->schemas?->has($name) ?? false;
    }

    public function schema(string $name): Schema|Reference|null
    {
        return $this->schemas?->get($name);
    }

    public function hasResponse(string $name): bool
    {
        return $this->responses?->has($name) ?? false;
    }

    public function response(string $name): Response|Reference|null
    {
        return $this->responses?->get($name);
    }

    public function hasParameter(string $name): bool
    {
        return $this->parameters?->has($name) ?? false;
    }

    public function parameter(string $name): Parameter|Reference|null
    {
        return $this->parameters?->get($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->schemas,
            $this->responses,
            $this->parameters,
            $this->examples,
            $this->requestBodies,
            $this->headers,
            $this->securitySchemes,
            $this->links,
            $this->callbacks,
            $this->pathItems,
            $this->mediaTypes,
            $extensions,
        );
    }
}
