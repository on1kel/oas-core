<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Collections\List\EncodingList;
use On1kel\OAS\Core\Model\Collections\Map\EncodingMap;
use On1kel\OAS\Core\Model\Collections\Map\HeaderMap;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Encoding Object (OAS 3.1 / 3.2)
 *
 * Определяет способ кодирования конкретного свойства или элемента тела запроса.
 * Применяется внутри MediaType и может быть вложен рекурсивно.
 *
 * 3.1 — базовые поля: contentType, headers, style, explode, allowReserved.
 * 3.2 — добавлены: prefixEncoding (list<Encoding>), itemEncoding (Encoding) и encoding (map<string, Encoding>).
 *
 * @see https://spec.openapis.org/oas/v3.1.0#encoding-object
 * @see https://spec.openapis.org/oas/v3.2.0#encoding-object
 */
final class Encoding implements Extensible
{
    use HasExtensions;

    /**
     * @param string|null         $contentType    MIME-тип, переопределяющий общий из RequestBody.
     * @param HeaderMap|null      $headers        Заголовки, применяемые к кодированному свойству.
     * @param string|null         $style          Стиль сериализации (matrix, form, simple и т. д.).
     * @param bool|null           $explode        Управляет разбиением списков/объектов.
     * @param bool|null           $allowReserved  Разрешает зарезервированные символы (для query-параметров).
     * @param EncodingList|null   $prefixEncoding (OAS 3.2) Кодирования для префиксных элементов.
     * @param Encoding|null       $itemEncoding   (OAS 3.2) Кодирование отдельного элемента коллекции.
     * @param EncodingMap|null    $encoding       (OAS 3.2) Вложенные кодирования, аналогичные MediaType.encoding.
     * @param array<string,mixed> $extensions     Расширения (x-*).
     */
    public function __construct(
        public readonly ?string $contentType = null,
        public readonly ?HeaderMap $headers = null,
        public readonly ?string $style = null,
        public readonly ?bool $explode = null,
        public readonly ?bool $allowReserved = null,
        public readonly ?EncodingList $prefixEncoding = null,
        public readonly ?Encoding $itemEncoding = null,
        public readonly ?EncodingMap $encoding = null,
        public readonly array $extensions = [],
    ) {
        self::assertExtensionsKeys($this->extensions);
    }


    public function hasContentType(): bool
    {
        return $this->contentType !== null && $this->contentType !== '';
    }

    public function hasHeaders(): bool
    {
        return $this->headers !== null && $this->headers->count() > 0;
    }

    public function hasStyle(): bool
    {
        return $this->style !== null && $this->style !== '';
    }

    public function hasPrefixEncoding(): bool
    {
        return $this->prefixEncoding !== null && $this->prefixEncoding->hasAny();
    }

    public function hasItemEncoding(): bool
    {
        return $this->itemEncoding !== null;
    }

    public function hasNestedEncoding(): bool
    {
        return $this->encoding !== null && $this->encoding->count() > 0;
    }


    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->contentType,
            $this->headers,
            $this->style,
            $this->explode,
            $this->allowReserved,
            $this->prefixEncoding,
            $this->itemEncoding,
            $this->encoding,
            $extensions,
        );
    }
}
