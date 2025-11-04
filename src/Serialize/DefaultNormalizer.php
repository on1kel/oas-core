<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Serialize;

use function array_diff;
use function array_is_list;
use function array_key_exists;
use function in_array;
use function is_array;
use function is_object;
use function is_scalar;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Serialize\Normalizer as NormalizerContract;
use ReflectionClass;
use ReflectionProperty;

use function str_starts_with;

use Traversable;

final class DefaultNormalizer implements NormalizerContract
{
    /** Пространства имён ядра */
    private const MODEL_NS = 'On1kel\\OAS\\Core\\Model\\';
    private const NS_LIST  = 'On1kel\\OAS\\Core\\Model\\Collections\\List\\';
    private const NS_MAP   = 'On1kel\\OAS\\Core\\Model\\Collections\\Map\\';
    private const NS_ENUM  = 'On1kel\\OAS\\Core\\Model\\Enum\\';

    /**
     * Ключи, которые считаем «служебными» у контейнеров с items.
     * Если кроме items есть только они — возвращаем именно items.
     */
    private const META_KEYS = [
        'summary','description','ref','servers','parameters','extensions',
        'deprecated','security','tags','externalDocs','operationId','callbacks',
        'jsonSchemaDialect','self',
    ];

    /**
     * Одноключевые обёртки внутри списков, которые разворачиваем.
     * Например, SecurityRequirementList → [{ "requirements": {...} }] → [{...}]
     */
    private const SINGLE_KEY_WRAPPERS = ['requirements'];

    public function supports(object $object): bool
    {
        return str_starts_with($object::class, self::MODEL_NS);
    }

    /** @return array<string,mixed> */
    public function normalize(object $object, SpecProfile $profile): array
    {
        return $this->normalizeObject($object, $profile, '#');
    }

    /** @return mixed */
    private function normalizeValue(mixed $value, SpecProfile $profile, string $ptr)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->normalizeValue($v, $profile, $this->childPtr($ptr, (string)$k));
            }

            return $this->postProcessNode($out, $ptr);
        }

        if ($value instanceof Traversable) {
            $tmp = [];
            foreach ($value as $k => $v) {
                $tmp[$k] = $this->normalizeValue($v, $profile, $this->childPtr($ptr, (string)$k));
            }

            return $this->postProcessNode($tmp, $ptr);
        }

        if (is_object($value)) {
            $class = $value::class;

            // Enum-модели → scalar value
            if (str_starts_with($class, self::NS_ENUM)) {
                $ref = new ReflectionClass($value);
                if ($ref->hasProperty('value')) {
                    $p = $ref->getProperty('value');
                    $p->setAccessible(true);

                    return $p->getValue($value);
                }

                return method_exists($value, '__toString') ? (string)$value : null;
            }

            // Коллекции
            if (str_starts_with($class, self::NS_LIST)) {
                return $this->normalizeList($value, $profile, $ptr);
            }
            if (str_starts_with($class, self::NS_MAP)) {
                return $this->normalizeMap($value, $profile, $ptr);
            }

            // Любая модель
            if (str_starts_with($class, self::MODEL_NS)) {
                return $this->normalizeObject($value, $profile, $ptr);
            }

            return method_exists($value, '__toString') ? (string)$value : null;
        }

        return null;
    }

    /** @return array<string,mixed> */
    /** @return array<string,mixed> */
    private function normalizeObject(object $obj, SpecProfile $profile, string $ptr): array
    {
        $ref = new ReflectionClass($obj);
        $out = [];

        /** @var ReflectionProperty $prop */
        foreach ($ref->getProperties() as $prop) {
            if (!$prop->isPublic()) {
                $prop->setAccessible(true);
            }
            $name  = $prop->getName();
            $value = $prop->getValue($obj);

            $childPtr   = $this->childPtr($ptr, $name);
            $out[$name] = $this->normalizeValue($value, $profile, $childPtr);
        }

        // Пост-обработка узла
        $out = $this->postProcessNode($out, $ptr);

        // Фильтрация ключей согласно профилю (ВАЖНО для 3.1 vs 3.2)
        $nodeType = $ref->getShortName(); // напр., "SecurityScheme", "Info", "Schema", ...
        $out = $this->filterAllowedKeys($out, $profile, $nodeType);

        return $out;
    }

    /**
     * Удаляет ключи, не разрешённые профилем для данного типа узла.
     * Поддерживает 'x-*' расширения, спец-правила для Responses и Paths.
     *
     * @param  array<string|int,mixed> $node
     * @return array<string,mixed>
     */
    private function filterAllowedKeys(array $node, SpecProfile $profile, string $nodeType): array
    {
        $allowed = $profile->allowedKeysFor($nodeType);
        if ($allowed === []) {
            return $node;
        }

        $allowExtensions = in_array('x-', $allowed, true);

        // ── Спец-случай: Paths — карта path-templates. Сохраняем ключи, начинающиеся с '/', и 'x-*'
        if ($nodeType === 'Paths') {
            $result = [];
            foreach ($node as $k => $v) {
                $kStr = (string) $k;
                if ($kStr !== '' && $kStr[0] === '/') {
                    $result[$kStr] = $v;
                    continue;
                }
                if ($allowExtensions && str_starts_with($kStr, 'x-')) {
                    $result[$kStr] = $v;
                }
                // всё остальное отбрасываем
            }

            return $result;
        }

        $result = [];
        foreach ($node as $k => $v) {
            $kStr = (string) $k;

            // ── Спец-случай: Responses допускает коды и 'default'
            if ($nodeType === 'Responses') {
                if (preg_match('/^[1-5][0-9]{2}$/', $kStr) === 1   // 100..599
                    || preg_match('/^[1-5]XX$/', $kStr) === 1      // 1XX..5XX
                    || $kStr === 'default') {
                    $result[$kStr] = $v;
                    continue;
                }
            }

            // Точное совпадение с разрешёнными ключами профиля
            if (in_array($kStr, $allowed, true)) {
                $result[$kStr] = $v;
                continue;
            }

            // Расширения 'x-*'
            if ($allowExtensions && str_starts_with($kStr, 'x-')) {
                $result[$kStr] = $v;
                continue;
            }
            // иначе — отбрасываем
        }

        return $result;
    }

    /** @return list<mixed> */
    private function normalizeList(object $obj, SpecProfile $profile, string $ptr): array
    {
        // BaseList: публичное/защищённое поле items (list<T>)
        $ref = new ReflectionClass($obj);
        if (!$ref->hasProperty('items')) {
            return [];
        }
        $p = $ref->getProperty('items');
        $p->setAccessible(true);
        $items = $p->getValue($obj);

        $out = [];
        if (is_array($items) || $items instanceof Traversable) {
            foreach ($items as $k => $v) {
                $out[] = $this->normalizeValue($v, $profile, $this->childPtr($ptr, (string)$k));
            }
        }

        // В т.ч. корректно обрабатывает list<string>
        return $this->postProcessList($out, $ptr);
    }

    /** @return array<string,mixed> */
    private function normalizeMap(object $obj, SpecProfile $profile, string $ptr): array
    {
        // BaseMap: публичное/защищённое поле items (assoc<string, T>)
        $ref = new ReflectionClass($obj);
        if (!$ref->hasProperty('items')) {
            return [];
        }
        $p = $ref->getProperty('items');
        $p->setAccessible(true);
        $items = $p->getValue($obj);

        $out = [];
        if (is_array($items) || $items instanceof Traversable) {
            foreach ($items as $k => $v) {
                $out[(string)$k] = $this->normalizeValue($v, $profile, $this->childPtr($ptr, (string)$k));
            }
        }

        return $out;
    }

    private function childPtr(string $parent, string $segment): string
    {
        $seg = str_replace(['~', '/'], ['~0', '~1'], $segment);

        return $parent === '#' ? "#/{$seg}" : "{$parent}/{$seg}";
    }

    // ───────────────────── Универсальная пост-обработка узлов ─────────────────────

    private function postProcessNode(array $node, string $ptr): array
    {
        // 0) extraKeywords → разворачиваем в корень узла
        if (array_key_exists('extraKeywords', $node) && is_array($node['extraKeywords'])) {
            foreach ($node['extraKeywords'] as $k => $v) {
                if (!array_key_exists($k, $node)) {
                    $node[$k] = $v;
                }
            }
            unset($node['extraKeywords']);
        }

        if (array_key_exists('ref', $node) && is_string($node['ref'])) {
            $node['$ref'] = $node['ref'];
            unset($node['ref']);
        }

        // 1) Контейнер с items (и только служебные поля помимо items) → схлопываем до items
        if (array_key_exists('items', $node)) {
            $other = array_diff(array_keys($node), ['items']);
            $allMeta = true;
            foreach ($other as $k) {
                if (!in_array($k, self::META_KEYS, true)) {
                    $allMeta = false;
                    break;
                }
            }
            if ($allMeta) {
                if (is_array($node['items']) && array_is_list($node['items'])) {
                    $processed = $this->postProcessList($node['items'], $ptr);

                    return $processed;
                }

                return is_array($node['items']) ? $node['items'] : $node;
            }
        }

        // 2) responses-обёртка → твоя логика
        if (array_key_exists('responses', $node) && is_array($node['responses'])) {
            $allowedOthers = ['default', 'extensions'];
            $other = array_diff(array_keys($node), ['responses', ...$allowedOthers]);
            if ($other === []) {
                $merged = [];
                if (array_key_exists('default', $node) && $node['default'] !== null) {
                    $merged['default'] = $node['default'];
                }
                foreach ($node['responses'] as $code => $resp) {
                    $merged[(string)$code] = $resp;
                }

                return $merged;
            }
        }

        return $node;
    }


    /**
     * @param  list<mixed> $list
     * @return list<mixed>
     */
    private function postProcessList(array $list, string $ptr): array
    {
        if ($list === [] || !array_is_list($list)) {
            return $list;
        }

        // 1) Список однотипных одноключевых обёрток → раскрываем
        $unwrapKey = null;
        $allSingleWrapper = true;

        foreach ($list as $item) {
            if (!is_array($item) || count($item) !== 1) {
                $allSingleWrapper = false;
                break;
            }
            $k = array_key_first($item);
            if (!in_array($k, self::SINGLE_KEY_WRAPPERS, true)) {
                $allSingleWrapper = false;
                break;
            }
            $unwrapKey ??= $k;
            if ($unwrapKey !== $k) {
                $allSingleWrapper = false;
                break;
            }
        }

        if ($allSingleWrapper && $unwrapKey !== null) {
            $out = [];
            foreach ($list as $item) {
                /** @var array{string: mixed} $item */
                $out[] = $item[$unwrapKey];
            }

            return $out;
        }

        // 2) Иначе — это уже корректный список (в т.ч. list<string>) → возвращаем как есть
        return $list;
    }
}
