<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\SchemaList;
use On1kel\OAS\Core\Model\Collections\Map\PatternSchemaMap;
use On1kel\OAS\Core\Model\Collections\Map\SchemaMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Schema Object (OAS 3.1 / 3.2) — гибридная модель
 *
 * JSON Schema 2020-12 (+ OAS-надстройки), с явными полями для структурных ключей
 * и "пасст-тру" для остальных через $extraKeywords. Поддерживает булевы схемы.
 *
 * Структурные ключи:
 *  - type: string|list<string>
 *  - enum: list<mixed>, const: mixed
 *  - allOf/anyOf/oneOf: list<Schema|Reference>, not: Schema|Reference|null
 *  - items: Schema|Reference|null, prefixItems: list<Schema|Reference>
 *  - properties: map<string, Schema|Reference>
 *  - patternProperties: map<pattern, Schema|Reference>
 *  - additionalProperties, unevaluatedProperties: Schema|bool|null
 *  - dependentSchemas: map<string, Schema|Reference>
 *  - required: list<string>
 *  - if/then/else: Schema|Reference|null
 *  - contentMediaType, contentEncoding: string|null; contentSchema: Schema|Reference|null
 *
 * OAS-специфичные:
 *  - nullable?: bool
 *  - readOnly/writeOnly?: bool
 *  - deprecated?: bool
 *  - discriminator?: Discriminator|null
 *  - xml?: Xml|null
 *  - externalDocs?: ExternalDocumentation|null
 *  - example?: mixed
 *  - examples?: list<mixed>
 *
 * Прочие ключевые слова (включая пользовательские словари) → $extraKeywords.
 * Расширения x-* → $extensions (единый DX для всего проекта).
 */
final class Schema implements Extensible
{
    use HasExtensions;

    // ---- Базовая форма ----
    public function __construct(
        // JSON Schema — структурные поля
        public readonly bool|array $raw = [],

        /** @var string|list<string>|null */
        public readonly string|array|null $type = null,

        /** @var list<mixed> */
        public readonly array $enum = [],
        public readonly mixed $const = null,
        public readonly ?SchemaList $allOf = null,
        public readonly ?SchemaList $anyOf = null,
        public readonly ?SchemaList $oneOf = null,
        public readonly Schema|Reference|null $not = null,
        public readonly Schema|Reference|null $items = null,
        public readonly ?SchemaList $prefixItems = null,
        public readonly ?SchemaMap $properties = null,
        public readonly ?PatternSchemaMap $patternProperties = null,

        /** @var Schema|bool|null */
        public readonly Schema|bool|null $additionalProperties = null,

        /** @var Schema|bool|null */
        public readonly Schema|bool|null $unevaluatedProperties = null,
        public readonly ?SchemaMap $dependentSchemas = null,

        /** @var list<string> */
        public readonly array $required = [],
        public readonly Schema|Reference|null $if = null,
        public readonly Schema|Reference|null $then = null,
        public readonly Schema|Reference|null $else = null,
        public readonly ?string $contentMediaType = null,
        public readonly ?string $contentEncoding = null,
        public readonly Schema|Reference|null $contentSchema = null,

        // OAS-specific (добавочные семантики поверх JSON Schema)
        public readonly ?bool $nullable = null,
        public readonly ?bool $readOnly = null,
        public readonly ?bool $writeOnly = null,
        public readonly ?bool $deprecated = null,
        public readonly ?Discriminator $discriminator = null,
        public readonly ?Xml $xml = null,
        public readonly ?ExternalDocumentation $externalDocs = null,
        public readonly mixed $example = null,
        /** @var list<mixed> */
        public readonly array $examples = [],

        /** Прочие ключевые слова (все нераспознанные) */
        /** @var array<string,mixed> */
        public readonly array $extraKeywords = [],

        /** Specification Extensions x-* */
        /** @var array<string,mixed> */
        public readonly array $extensions = [],
    ) {
        // 0) Булева схема допускается (true/false). Иначе — массив ключевых слов.
        if (!\is_bool($this->raw) && !\is_array($this->raw)) {
            throw new \InvalidArgumentException('Schema: $raw должен быть массивом или булевым значением.');
        }

        // 1) Валидация enum/required/examples как списков
        if (!\array_is_list($this->enum)) {
            throw new \InvalidArgumentException('Schema: "enum" должен быть списком.');
        }
        if (!\array_is_list($this->required)) {
            throw new \InvalidArgumentException('Schema: "required" должен быть списком строк.');
        }
        foreach ($this->required as $rq) {
            if (!\is_string($rq) || $rq === '') {
                throw new \InvalidArgumentException('Schema: каждый элемент "required" должен быть непустой строкой.');
            }
        }
        if (!\array_is_list($this->examples)) {
            throw new \InvalidArgumentException('Schema: "examples" должен быть списком.');
        }

        // 2) type: string|list<string>
        if ($this->type !== null) {
            if (\is_array($this->type)) {
                if (!\array_is_list($this->type)) {
                    throw new \InvalidArgumentException('Schema: "type" как список должен быть list<string>.');
                }
                foreach ($this->type as $t) {
                    if (!\is_string($t) || $t === '') {
                        throw new \InvalidArgumentException('Schema: элементы "type" должны быть непустыми строками.');
                    }
                }
            } elseif ($this->type === '') {
                throw new \InvalidArgumentException('Schema: "type", если строка, не может быть пустой.');
            }
        }

        // 3) additionalProperties / unevaluatedProperties: Schema|bool|null — ок по типам конструктора.

        // 4) Расширения x-* — по общей политике
        self::assertExtensionsKeys($this->extensions);
    }

    // ---- Утилиты ----

    /** Булева схема (true/false)? */
    public function isBooleanSchema(): bool
    {
        return \is_bool($this->raw);
    }

    /** Есть ли структурное свойство среди "известных". */
    public function hasStructural(string $field): bool
    {
        return \property_exists($this, $field) && $this->{$field} !== null;
    }

    /** Доступ к extraKeywords (неструктурные слова). */
    public function extra(string $keyword): mixed
    {
        return $this->extraKeywords[$keyword] ?? null;
    }

    /** Есть ли extra-ключевое слово. */
    public function hasExtra(string $keyword): bool
    {
        return \array_key_exists($keyword, $this->extraKeywords);
    }

    /** @return array<string,mixed> */
    public function allExtra(): array
    {
        return $this->extraKeywords;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->raw,
            $this->type,
            $this->enum,
            $this->const,
            $this->allOf,
            $this->anyOf,
            $this->oneOf,
            $this->not,
            $this->items,
            $this->prefixItems,
            $this->properties,
            $this->patternProperties,
            $this->additionalProperties,
            $this->unevaluatedProperties,
            $this->dependentSchemas,
            $this->required,
            $this->if,
            $this->then,
            $this->else,
            $this->contentMediaType,
            $this->contentEncoding,
            $this->contentSchema,
            $this->nullable,
            $this->readOnly,
            $this->writeOnly,
            $this->deprecated,
            $this->discriminator,
            $this->xml,
            $this->externalDocs,
            $this->example,
            $this->examples,
            $this->extraKeywords,
            $extensions,
        );
    }
}
