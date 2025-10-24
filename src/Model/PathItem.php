<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\ParameterList;
use On1kel\OAS\Core\Model\Collections\List\ServerList;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Path Item Object (OAS 3.1 / 3.2)
 *
 * Может содержать $ref ИЛИ инлайн-поля. По спецификации в 3.1/3.2 $ref
 * допускается вместе с другими полями — оставляем все поля опциональными.
 */
final class PathItem implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null         $ref         Ссылка на Path Item (если используется $ref)
     * @param string|null         $summary     Краткое описание
     * @param string|null         $description Подробное описание
     * @param Operation|null      $get
     * @param Operation|null      $put
     * @param Operation|null      $post
     * @param Operation|null      $delete
     * @param Operation|null      $options
     * @param Operation|null      $head
     * @param Operation|null      $patch
     * @param Operation|null      $trace
     * @param ServerList|null     $servers     Переопределение серверов на уровне пути
     * @param ParameterList|null  $parameters  Общие параметры для всех операций пути
     * @param array<string,mixed> $extensions  x-расширения
     */
    public function __construct(
        public readonly ?string $ref = null,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?Operation $get = null,
        public readonly ?Operation $put = null,
        public readonly ?Operation $post = null,
        public readonly ?Operation $delete = null,
        public readonly ?Operation $options = null,
        public readonly ?Operation $head = null,
        public readonly ?Operation $patch = null,
        public readonly ?Operation $trace = null,
        public readonly ?ServerList $servers = null,
        public readonly ?ParameterList $parameters = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
        // Никаких дополнительных ограничений здесь не вводим — всё уедет в валидаторы профиля.
    }

    public function hasOperations(): bool
    {
        return $this->get || $this->put || $this->post || $this->delete || $this->options
            || $this->head || $this->patch || $this->trace;
    }

    /**
     * {@inheritDoc}
     */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->ref,
            $this->summary,
            $this->description,
            $this->get,
            $this->put,
            $this->post,
            $this->delete,
            $this->options,
            $this->head,
            $this->patch,
            $this->trace,
            $this->servers,
            $this->parameters,
            $extensions,
        );
    }
}
