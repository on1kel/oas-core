<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\SecurityRequirementList;
use On1kel\OAS\Core\Model\Collections\List\ServerList;
use On1kel\OAS\Core\Model\Collections\List\TagList;
use On1kel\OAS\Core\Model\Collections\Map\WebhookMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Корневой объект OpenAPI документа.
 * Совместим с OAS 3.1/3.2 (доп. различия — через SpecProfile).
 */
final class OpenApiDocument implements Extensible
{
    use HasExtensions;

    /**
     * @param string $openapi Версия OAS, например "3.1.0" или "3.2.0"
     * @param Info $info Блок информации
     * @param string|null $jsonSchemaDialect Диалект JSON Schema (3.1+)
     * @param string|null $self канонический URI самого документа. База для разрешения относительных ссылок внутри описания
     * @param Paths|null $paths Карта путей
     * @param WebhookMap|null $webhooks Карта вебхуков (3.1+)
     * @param Components|null $components Именованные компоненты
     * @param ServerList|null $servers Список серверов
     * @param SecurityRequirementList|null $security Требования безопасности (массив карт)
     * @param TagList|null $tags Теги
     * @param ExternalDocumentation|null $externalDocs Внешняя документация
     * @param array<string,mixed> $extensions x-расширения на корневом уровне
     */
    public function __construct(
        public readonly string $openapi,
        public readonly Info $info,
        public readonly ?string $jsonSchemaDialect = null,
        public readonly ?string $self = null, // 3.2+
        public readonly ?Paths $paths = null,
        public readonly ?WebhookMap $webhooks = null,
        public readonly ?Components $components = null,
        public readonly ?ServerList $servers = null,
        public readonly ?SecurityRequirementList $security = null,
        public readonly ?TagList $tags = null,
        public readonly ?ExternalDocumentation $externalDocs = null,
        public readonly array $extensions = [],
    ) {
        if ($openapi === '') {
            throw new \InvalidArgumentException('Поле "openapi" не может быть пустым.');
        }

        if ($this->info === null) {
            throw new \InvalidArgumentException('Поле "info" не может быть пустым.');
        }

        self::assertExtensionsKeys($this->extensions);
    }

    /**
     * @inheritDoc
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->openapi,
            $this->info,
            $this->jsonSchemaDialect,
            $this->self,
            $this->paths,
            $this->webhooks,
            $this->components,
            $this->servers,
            $this->security,
            $this->tags,
            $this->externalDocs,
            $extensions,
        );
    }
}
