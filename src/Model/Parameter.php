<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\ExampleMap;
use On1kel\OAS\Core\Model\Collections\Map\MediaTypeMap;
use On1kel\OAS\Core\Model\Enum\ParameterIn;
use On1kel\OAS\Core\Model\Enum\Style;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Parameter Object (OAS 3.1 / 3.2)
 *
 * Требования и нюансы:
 * - REQUIRED: name, in
 * - Для in=path: required ДОЛЖЕН быть true
 * - либо (schema/example/examples), либо (content), но НЕ одновременно
 * - example и examples взаимоисключают друг друга
 * - allowEmptyValue, allowReserved применимы ТОЛЬКО к query
 * - style по умолчанию зависит от in:
 *   query/cookie => form; path/header => simple
 * - explode по умолчанию: true если style=form, иначе false
 */
final class Parameter implements Extensible
{
    use HasExtensions;

    /**
     * @param string                $name
     * @param ParameterIn           $in
     * @param string|null           $description
     * @param bool                  $required        (для path ДОЛЖЕН быть true)
     * @param bool                  $deprecated
     * @param bool|null             $allowEmptyValue (только для query)
     * @param Style|null            $style           (дефолт зависит от in)
     * @param bool|null             $explode         (дефолт зависит от style)
     * @param bool|null             $allowReserved   (только для query)
     * @param Schema|Reference|null $schema
     * @param mixed|null            $example         (взаимоисключает examples)
     * @param ExampleMap|null       $examples        (взаимоисключает example)
     * @param MediaTypeMap|null     $content         (взаимоисключает schema/example/examples)
     * @param array<string,mixed>   $extensions
     */
    public function __construct(
        public readonly string $name,
        public readonly ParameterIn $in,
        public readonly ?string $description = null,
        public readonly bool $required = false,
        public readonly bool $deprecated = false,
        public readonly ?bool $allowEmptyValue = null,
        public readonly ?Style $style = null,
        public readonly ?bool $explode = null,
        public readonly ?bool $allowReserved = null,
        public readonly Schema|Reference|null $schema = null,
        public readonly mixed $example = null,
        public readonly ?ExampleMap $examples = null,
        public readonly ?MediaTypeMap $content = null,
        public readonly array $extensions = [],
    ) {
        if ($this->name === '') {
            throw new \InvalidArgumentException('Parameter: "name" не может быть пустым.');
        }
        self::assertExtensionsKeys($this->extensions);

        // 1) path → required MUST be true
        if ($this->in === ParameterIn::Path && $this->required !== true) {
            throw new \InvalidArgumentException('Parameter: для in=path поле "required" ДОЛЖНО быть true.');
        }

        // 2) example XOR examples
        if ($this->example !== null && $this->examples !== null) {
            throw new \InvalidArgumentException('Parameter: "example" и "examples" взаимоисключают друг друга.');
        }

        // 3) schema/example(s) XOR content
        $hasSchemaBlock  = $this->schema !== null || $this->example !== null || $this->examples !== null;
        $hasContentBlock = $this->content !== null;
        if ($hasSchemaBlock && $hasContentBlock) {
            throw new \InvalidArgumentException('Parameter: нельзя указывать одновременно schema/example(s) и content.');
        }

        // 4) allowEmptyValue / allowReserved — только для query
        if ($this->in !== ParameterIn::Query && $this->allowEmptyValue !== null) {
            throw new \InvalidArgumentException('Parameter: "allowEmptyValue" допустим только для in=query.');
        }
        if ($this->in !== ParameterIn::Query && $this->allowReserved !== null) {
            throw new \InvalidArgumentException('Parameter: "allowReserved" допустим только для in=query.');
        }
    }

    /**
     * Эффективный стиль: явный $style или дефолт по спецификации.
     */
    public function effectiveStyle(): Style
    {
        if ($this->style !== null) {
            return $this->style;
        }

        return match ($this->in) {
            ParameterIn::Query, ParameterIn::Cookie => Style::Form,
            ParameterIn::Path, ParameterIn::Header => Style::Simple,
        };
    }

    /**
     * Эффективное explode: явный $explode или дефолт (true для form, иначе false).
     */
    public function effectiveExplode(): bool
    {
        if ($this->explode !== null) {
            return $this->explode;
        }

        return $this->effectiveStyle() === Style::Form;
    }

    /** {@inheritDoc} */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->name,
            $this->in,
            $this->description,
            $this->required,
            $this->deprecated,
            $this->allowEmptyValue,
            $this->style,
            $this->explode,
            $this->allowReserved,
            $this->schema,
            $this->example,
            $this->examples,
            $this->content,
            $extensions,
        );
    }
}
