<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Model\Support\HasExtensions;

/**
 * Info Object (OAS 3.1 / 3.2)
 *
 * Основные метаданные API: заголовок, описание, версия, контакт и лицензия.
 * Все поля иммутабельны, null — если отсутствуют.
 */
final class Info implements Extensible
{
    use HasExtensions;
    /**
     * @param string              $title          Название API (обязательное)
     * @param string              $version        Версия API (обязательное)
     * @param string|null         $summary        Краткое описание (3.1+)
     * @param string|null         $description    Подробное описание
     * @param string|null         $termsOfService URI условий использования
     * @param Contact|null        $contact        Контактное лицо/организация
     * @param License|null        $license        Лицензия
     * @param array<string,mixed> $extensions     Specification Extensions (ключи "x-*")
     */
    public function __construct(
        public readonly string $title,
        public readonly string $version,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?string $termsOfService = null,
        public readonly ?Contact $contact = null,
        public readonly ?License $license = null,
        public readonly array $extensions = [],
    ) {
        if ($title === '') {
            throw new \InvalidArgumentException('Поле "title" не может быть пустым.');
        }
        if ($version === '') {
            throw new \InvalidArgumentException('Поле "version" не может быть пустым.');
        }

        self::assertExtensionsKeys($this->extensions);
    }

    /** @inheritDoc */
    protected function recreateWithExtensions(array $extensions): static
    {
        return new self(
            $this->title,
            $this->version,
            $this->summary,
            $this->description,
            $this->termsOfService,
            $this->contact,
            $this->license,
            $extensions,
        );
    }
}
