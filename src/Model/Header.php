<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\Map\ExampleMap;
use On1kel\OAS\Core\Model\Collections\Map\MediaTypeMap;
use On1kel\OAS\Core\Model\Enum\Style;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Header Object (OAS 3.1 / 3.2)
 *
 * Описывает один HTTP-заголовок (для Response.headers и Encoding.headers).
 * Следует структуре Parameter, НО:
 *  - name и in НЕ указываются (берутся из карты/по умолчанию header);
 *  - для header разрешён только style="simple";
 *  - allowEmptyValue / allowReserved НЕ применяются.
 *
 * @see https://spec.openapis.org/oas/v3.2.0.html#header-object
 * @see https://spec.openapis.org/oas/v3.1.0#header-object
 */
final class Header implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null           $description
     * @param bool                  $required    По умолчанию false.
     * @param bool                  $deprecated  По умолчанию false.
     * @param Style|null            $style       Если задан, ДОЛЖЕН быть Style::Simple.
     * @param bool|null             $explode     Для header по умолчанию false.
     * @param Schema|Reference|null $schema      Ветка "schema"-сериализации (взаимоисключает content).
     * @param mixed|null            $example     Взаимоисключает examples.
     * @param ExampleMap|null       $examples    Взаимоисключает example.
     * @param MediaTypeMap|null     $content     Ветка "content"-сериализации (взаимоисключает schema/example(s)); карта ДОЛЖНА содержать ровно один элемент.
     * @param array<string,mixed>   $extensions  x-* расширения.
     */
    public function __construct(
        public readonly ?string $description = null,
        public readonly bool $required = false,
        public readonly bool $deprecated = false,
        public readonly ?Style $style = null,
        public readonly ?bool $explode = null,
        public readonly Schema|Reference|null $schema = null,
        public readonly mixed $example = null,
        public readonly ?ExampleMap $examples = null,
        public readonly ?MediaTypeMap $content = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);

        // 1) example XOR examples
        if ($this->example !== null && $this->examples !== null) {
            throw new \InvalidArgumentException('Header: "example" и "examples" взаимоисключают друг друга.');
        }

        // 2) schema/example(s) XOR content
        $hasSchemaBlock  = $this->schema !== null || $this->example !== null || $this->examples !== null;
        $hasContentBlock = $this->content !== null;
        if ($hasSchemaBlock && $hasContentBlock) {
            throw new \InvalidArgumentException('Header: нельзя указывать одновременно schema/example(s) и content.');
        }

        // 3) Если content задан — ДОЛЖЕН быть ровно один media type
        if ($this->content !== null && $this->content->count() !== 1) {
            throw new \InvalidArgumentException('Header: поле "content" ДОЛЖНО содержать ровно один элемент.');
        }

        // 4) Для заголовков допустим только style=simple (если явно задан)
        if ($this->style !== null && $this->style !== Style::Simple) {
            throw new \InvalidArgumentException('Header: для заголовков допустим только style="simple".');
        }
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    /**
     * Эффективный style: всегда Simple (для headers).
     */
    public function effectiveStyle(): Style
    {
        return Style::Simple;
    }

    /**
     * Эффективный explode: по умолчанию false для headers (style=simple).
     */
    public function effectiveExplode(): bool
    {
        return $this->explode ?? false;
    }

    public function hasSchema(): bool
    {
        return $this->schema !== null;
    }

    public function hasContent(): bool
    {
        return $this->content !== null;
    }

    public function hasExamples(): bool
    {
        return ($this->examples?->count() ?? 0) > 0 || $this->example !== null;
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->description,
            $this->required,
            $this->deprecated,
            $this->style,
            $this->explode,
            $this->schema,
            $this->example,
            $this->examples,
            $this->content,
            $extensions,
        );
    }
}
