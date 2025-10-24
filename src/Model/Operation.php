<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\ParameterList;
use On1kel\OAS\Core\Model\Collections\List\SecurityRequirementList;
use On1kel\OAS\Core\Model\Collections\List\ServerList;
use On1kel\OAS\Core\Model\Collections\List\TagList;
use On1kel\OAS\Core\Model\Collections\Map\CallbackMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Operation Object (OAS 3.1 / 3.2)
 *
 * По практике и правилам инструментов у операции должен быть хотя бы один ответ —
 * проверка на «непустоту» будет в валидаторе профиля. Здесь только структура.
 */
final class Operation implements Extensible
{
    use HasExtensions;

    /**
     * @param TagList|null $tags
     * @param string|null $summary
     * @param string|null $description
     * @param ExternalDocumentation|null $externalDocs
     * @param string|null $operationId
     * @param ParameterList|null $parameters
     * @param RequestBody|Reference|null $requestBody
     * @param Responses $responses
     * @param CallbackMap|null $callbacks
     * @param bool $deprecated
     * @param SecurityRequirementList|null $security
     * @param ServerList|null $servers
     * @param array<string,mixed> $extensions
     */
    public function __construct(
        public readonly ?TagList $tags,
        public readonly ?string $summary,
        public readonly ?string $description,
        public readonly ?ExternalDocumentation $externalDocs,
        public readonly ?string $operationId,
        public readonly ?ParameterList $parameters,
        public readonly RequestBody|Reference|null $requestBody,
        public readonly Responses $responses,
        public readonly ?CallbackMap $callbacks,
        public readonly bool $deprecated = false,
        public readonly ?SecurityRequirementList $security = null,
        public readonly ?ServerList $servers = null,
        public readonly array $extensions = [],
    ) {
        if ($this->operationId === '') {
            throw new \InvalidArgumentException('Если задан, "operationId" не может быть пустой строкой.');
        }
        self::assertExtensionsKeys($this->extensions);
    }

    public function hasParameters(): bool
    {
        return $this->parameters?->hasAny() ?? false;
    }

    public function hasCallbacks(): bool
    {
        return ($this->callbacks?->count() ?? 0) > 0;
    }

    public function hasTags(): bool
    {
        return $this->tags?->hasAny() ?? false;
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->tags,
            $this->summary,
            $this->description,
            $this->externalDocs,
            $this->operationId,
            $this->parameters,
            $this->requestBody,
            $this->responses,
            $this->callbacks,
            $this->deprecated,
            $this->security,
            $this->servers,
            $extensions,
        );
    }
}
