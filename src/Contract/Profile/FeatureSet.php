<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Profile;

/**
 * Набор фич-флагов профиля версии. Значения читаются компонентами ядра,
 * чтобы менять поведение без ветвления по версиям в пользовательском коде.
 */
final class FeatureSet
{
    /**
     * @param bool                           $jsonSchemaDraft2020_12   Поддерживается ли JSON Schema draft 2020-12 (в OAS 3.1 — да)
     * @param bool                           $webhooksSupported        Поддерживаются ли webhooks на уровне спецификации
     * @param bool                           $examplesAtMediaTypeLevel Разрешены/используются ли examples на уровне MediaType
     * @param array<string, bool|int|string> $extra                    Расширяемый словарь флагов (без ломающих изменений)
     */
    public function __construct(
        public readonly bool  $jsonSchemaDraft2020_12,
        public readonly bool  $webhooksSupported,
        public readonly bool  $examplesAtMediaTypeLevel,
        public readonly array $extra = [],
    ) {
    }

    /**
     * Быстрый доступ к произвольному флагу из $extra.
     */
    public function extraFlag(string $name, bool|int|string|null $default = null): bool|int|string|null
    {
        return $this->extra[$name] ?? $default;
    }
}
