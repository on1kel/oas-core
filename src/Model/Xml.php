<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Enum\XmlNodeType;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * XML Object (OAS 3.1 / 3.2)
 *
 * 3.1: name, namespace, prefix, attribute, wrapped
 * 3.2: добавлен nodeType (XmlNodeType),
 *      при его наличии attribute и wrapped запрещены (MUST NOT).
 *
 * @see https://spec.openapis.org/oas/v3.1.0#xml-object
 * @see https://spec.openapis.org/oas/v3.2.0.html#xml-object
 */
final class Xml implements Extensible
{
    use HasExtensions;

    /**
     * @param XmlNodeType|null    $nodeType   (3.2) тип XML-узла.
     * @param string|null         $name       Имя элемента/атрибута.
     * @param string|null         $namespace  Non-relative IRI пространства имён.
     * @param string|null         $prefix     Префикс имени.
     * @param bool|null           $attribute  (3.1) сериализация как атрибут (deprecated в 3.2).
     * @param bool|null           $wrapped    (3.1) обёртка массива (deprecated в 3.2).
     * @param array<string,mixed> $extensions Расширения (x-*).
     */
    public function __construct(
        public readonly ?XmlNodeType $nodeType = null,
        public readonly ?string $name = null,
        public readonly ?string $namespace = null,
        public readonly ?string $prefix = null,
        public readonly ?bool $attribute = null,
        public readonly ?bool $wrapped = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);

        // 3.2: nodeType => запрещает attribute и wrapped.
        if ($this->nodeType !== null) {
            if ($this->attribute !== null) {
                throw new \InvalidArgumentException('Xml: при наличии nodeType нельзя указывать "attribute".');
            }
            if ($this->wrapped !== null) {
                throw new \InvalidArgumentException('Xml: при наличии nodeType нельзя указывать "wrapped".');
            }
        }

        // Базовые проверки на пустые строки.
        if ($this->name !== null && $this->name === '') {
            throw new \InvalidArgumentException('Xml: "name", если задано, не может быть пустым.');
        }
        if ($this->namespace !== null && $this->namespace === '') {
            throw new \InvalidArgumentException('Xml: "namespace", если задано, не может быть пустым.');
        }
        if ($this->prefix !== null && $this->prefix === '') {
            throw new \InvalidArgumentException('Xml: "prefix", если задан, не может быть пустым.');
        }
    }

    public function hasName(): bool
    {
        return $this->name !== null && $this->name !== '';
    }

    public function hasNamespace(): bool
    {
        return $this->namespace !== null && $this->namespace !== '';
    }

    public function hasPrefix(): bool
    {
        return $this->prefix !== null && $this->prefix !== '';
    }

    public function hasNodeType(): bool
    {
        return $this->nodeType !== null;
    }

    public function isAttributeFlagSet(): bool
    {
        return $this->attribute === true;
    }

    public function isWrappedFlagSet(): bool
    {
        return $this->wrapped === true;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->nodeType,
            $this->name,
            $this->namespace,
            $this->prefix,
            $this->attribute,
            $this->wrapped,
            $extensions,
        );
    }
}
