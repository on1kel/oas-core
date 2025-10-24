<?php

// src/Ref/UriHelper.php (фрагмент)
declare(strict_types=1);

namespace On1kel\OAS\Core\Ref;

final class UriHelper
{
    private function __construct()
    {
    }

    /**
     * Строит стабильный ключ кеша для пары (baseUri, ref).
     * Нормализует относительный ref в абсолютный resolvedUri и добавляет pointer.
     */
    public static function makeCacheKey(string $baseUri, string $ref): string
    {
        [$targetUri, $pointer] = self::splitRef($baseUri, $ref);

        return $targetUri . '#' . $pointer;
    }

    /**
     * Разбивает $ref на абсолютный URI целевого документа и JSON Pointer.
     *
     * Примеры:
     *  baseUri="http://host/root.json", ref="#/Foo"
     *    → ["http://host/root.json", "/Foo"]
     *
     *  baseUri="http://host/root.json", ref="models/user.json#/User"
     *    → ["http://host/models/user.json", "/User"]
     *
     *  baseUri="file:///var/spec/root.json", ref="../common.json#/X"
     *    → ["file:///var/common.json", "/X"]
     *
     * Если во входном ref нет '#', pointer = "" (пустая строка).
     *
     * @return array{0:string,1:string} [resolvedTargetUri, pointerWithoutHash]
     */
    public static function splitRef(string $baseUri, string $ref): array
    {
        // Разделяем на "pathPart" и "fragment"
        $hashPos = strpos($ref, '#');
        $pathPart = $hashPos === false ? $ref : substr($ref, 0, $hashPos);
        $rawFragment = $hashPos === false ? '' : substr($ref, $hashPos + 1); // без '#'

        // Абсолютный URI целевого документа:
        // Если pathPart пустой -> это локальная ссылка типа "#/components/..." -> просто baseUri
        if ($pathPart === '') {
            $resolvedUri = $baseUri;
        } else {
            $resolvedUri = self::resolveRelative($baseUri, $pathPart);
            // resolveRelative может вернуть что-то с фрагментом, а нам нужно тело документа без фрагмента.
            // Поэтому отрезаем кусок после '#', если он внезапно есть.
            $againHashPos = strpos($resolvedUri, '#');
            if ($againHashPos !== false) {
                $resolvedUri = substr($resolvedUri, 0, $againHashPos);
            }
        }

        // pointer — это JSON Pointer в формате RFC6901.
        // Если фрагмент пустой -> pointer пустой.
        // Если фрагмент не начинается с '/', мы всё равно возвращаем как есть.
        // (валидация делается в другом месте)
        $pointer = $rawFragment;

        return [$resolvedUri, $pointer];
    }

    public static function resolveRelative(string $baseUri, string $ref): string
    {
        $hashPos  = strpos($ref, '#');
        $pathPart = $hashPos === false ? $ref : substr($ref, 0, $hashPos);
        $fragment = $hashPos === false ? '' : substr($ref, $hashPos);

        // чисто якорь -> остаёмся в том же документе
        if ($pathPart === '') {
            return $baseUri . $fragment;
        }

        $parts  = parse_url($baseUri) ?: [];
        $scheme = $parts['scheme'] ?? '';

        if ($scheme === 'http' || $scheme === 'https') {
            $host = $parts['host'] ?? '';
            $port = isset($parts['port']) ? ':' . (string)$parts['port'] : '';
            $path = $parts['path'] ?? '';
            // basePath — директория baseUri
            $basePath = ($path === '' || str_ends_with($path, '/'))
                ? $path
                : substr($path, 0, (int)strrpos($path, '/') + 1);

            $norm = self::normalizePath($basePath . $pathPart);

            // query не переносим (по спецификации относительный ref заменяет путь целиком)
            $prefix = $parts['scheme'] . '://' . $host . $port;

            return $prefix . $norm . $fragment;
        }

        if ($scheme === 'file') {
            $path = $parts['path'] ?? '';
            $dir  = ($path === '' || str_ends_with($path, '/'))
                ? $path
                : substr($path, 0, (int)strrpos($path, '/') + 1);

            $joined = self::normalizePath(($dir !== '' ? $dir : '/') . $pathPart);

            return 'file://' . $joined . $fragment; // <-- конкатенация через "."
        }

        // Локальные абсолютные/относительные пути без схемы
        if ($scheme === '') {
            // Если baseUri — локальный файл/путь (без схемы), учитываем директорию baseUri
            $baseDir = '';
            if ($baseUri !== '' && !str_contains($baseUri, '://')) {
                if (is_dir($baseUri)) {
                    $baseDir = rtrim($baseUri, '/\\') . '/';
                } else {
                    $pos = strrpos($baseUri, '/');
                    if ($pos !== false) {
                        $baseDir = substr($baseUri, 0, $pos + 1);
                    }
                }
            }
            $joined = self::normalizePath($baseDir . $pathPart);

            return $joined . $fragment;
        }

        // По умолчанию возвращаем исходный ref
        return $ref;
    }

    private static function normalizePath(string $path): string
    {
        $segments = explode('/', $path);
        $stack = [];
        foreach ($segments as $seg) {
            if ($seg === '' || $seg === '.') {
                if ($seg === '' && empty($stack)) {
                    $stack[] = '';
                }
                continue;
            }
            if ($seg === '..') {
                if (!empty($stack) && end($stack) !== '') {
                    array_pop($stack);
                }
                continue;
            }
            $stack[] = $seg;
        }
        // Корневая слэш-путь
        $normalized = implode('/', $stack);

        return $normalized === '' ? '/' : $normalized;
    }

    /**
     * RFC 6901: '/' — это ключ '' у корневого объекта, а не «весь документ»
     */
    public static function getByPointer(array $doc, string $pointer): mixed
    {
        if ($pointer === '') {
            return $doc;
        }
        if (!str_starts_with($pointer, '/')) {
            $pointer = '/' . $pointer;
        }

        $parts = explode('/', $pointer);
        array_shift($parts); // убрать ведущий пустой сегмент

        $node = $doc;
        foreach ($parts as $token) {
            $key = str_replace(['~1', '~0'], ['/', '~'], $token);
            if (is_array($node) && array_key_exists($key, $node)) {
                $node = $node[$key];
                continue;
            }
            if (is_array($node) && ctype_digit($key)) {
                $idx = (int)$key;
                if (array_key_exists($idx, $node)) {
                    $node = $node[$idx];
                    continue;
                }
            }

            return null; // не найдено
        }

        return $node;
    }
}
