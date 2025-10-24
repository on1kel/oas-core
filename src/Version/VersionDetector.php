<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Version;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;

/**
 * Определяет профиль по содержимому документа.
 * Логика:
 *  1) Берём строку в корне 'openapi' (например, '3.1.0', '3.2.0').
 *  2) Приводим к "major.minor" => '3.1'/'3.2'.
 *  3) Если такой профиль есть в реестре — возвращаем его; иначе — дефолт.
 */
final class VersionDetector
{
    /**
     * @param array<mixed> $rawDoc Корневой ассоц. массив OpenAPI
     */
    public function detect(array $rawDoc, ProfileRegistry $registry): SpecProfile
    {
        $ver = $this->extractOpenApiVersion($rawDoc);
        if ($ver !== null) {
            // Нормализуем '3.1.0' → '3.1'
            $normalized = $this->normalizeVersion($ver);
            if ($normalized !== null && $registry->has($normalized)) {
                return $registry->get($normalized);
            }
        }

        return $registry->getDefault();
    }

    /**
     * @param array<mixed> $rawDoc
     */
    private function extractOpenApiVersion(array $rawDoc): ?string
    {
        $v = $rawDoc['openapi'] ?? null;

        return is_string($v) && $v !== '' ? $v : null;
    }

    private function normalizeVersion(string $ver): ?string
    {
        // Оставляем только major.minor, если это 3.x (для будущих версий можно расширить)
        if (!preg_match('/^\s*(\d+)\.(\d+)(?:\.\d+)?\s*$/', $ver, $m)) {
            return null;
        }

        return $m[1] . '.' . $m[2];
    }
}
