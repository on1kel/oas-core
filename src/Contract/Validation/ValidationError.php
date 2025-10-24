<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;

/**
 * DTO одной проблемы, найденной валидатором.
 */
final class ValidationError
{
    /**
     * @param string      $pointer       JSON Pointer до узла (например, "#/paths/~1pets/get")
     * @param string      $code          Стабильный машинный код ошибки (например, "operation.responses.missing")
     * @param string      $message       Человекочитаемое описание
     * @param Severity    $severity      Уровень важности
     * @param string|null $sourceVersion Каким профилем выявлено (например, "3.1" или "3.2")
     * @param string|null $hint          Подсказка/рекомендация по исправлению
     */
    public function __construct(
        public readonly string   $pointer,
        public readonly string   $code,
        public readonly string   $message,
        public readonly Severity $severity = Severity::Error,
        public readonly ?string  $sourceVersion = null,
        public readonly ?string  $hint = null,
    ) {
    }

    /**
     * Быстрые фабрики.
     */
    public static function error(string $pointer, string $code, string $message, ?string $hint = null, ?string $sourceVersion = null): self
    {
        return new self($pointer, $code, $message, Severity::Error, $sourceVersion, $hint);
    }

    public static function warning(string $pointer, string $code, string $message, ?string $hint = null, ?string $sourceVersion = null): self
    {
        return new self($pointer, $code, $message, Severity::Warning, $sourceVersion, $hint);
    }

    public static function info(string $pointer, string $code, string $message, ?string $hint = null, ?string $sourceVersion = null): self
    {
        return new self($pointer, $code, $message, Severity::Info, $sourceVersion, $hint);
    }
}
