<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation;

use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;

use function str_replace;

/**
 * Контекст, доступный правилам-валидаторам при проверке.
 * Содержит профиль, режим строгости, базовый URI и текущий JSON Pointer.
 */
final class ValidationContext
{
    /**
     * @param SpecProfile $profile    Активный профиль спецификации (например, 3.1 или 3.2)
     * @param Strictness  $strictness Режим строгости (Strict/Lenient)
     * @param string|null $baseUri    Базовый URI документа (для референсов/логирования)
     * @param string      $pointer    Текущий JSON Pointer (по умолчанию '#')
     */
    public function __construct(
        private readonly SpecProfile $profile,
        private readonly Strictness  $strictness,
        private readonly ?string     $baseUri = null,
        private readonly string      $pointer = '#',
    ) {
    }

    public function profile(): SpecProfile
    {
        return $this->profile;
    }

    public function strictness(): Strictness
    {
        return $this->strictness;
    }

    public function baseUri(): ?string
    {
        return $this->baseUri;
    }

    public function pointer(): string
    {
        return $this->pointer;
    }

    /**
     * Вернуть новый контекст с обновлённым JSON Pointer.
     */
    public function withPointer(string $pointer): self
    {
        return new self($this->profile, $this->strictness, $this->baseUri, $pointer);
    }

    /**
     * Создать дочерний pointer: текущий + "/{segment}" (экранируя по JSON Pointer правилам).
     */
    public function child(string $segment): self
    {
        $escaped = str_replace(['~', '/'], ['~0', '~1'], $segment);
        $ptr = $this->pointer === '#' ? "#/{$escaped}" : "{$this->pointer}/{$escaped}";

        return $this->withPointer($ptr);
    }
}
