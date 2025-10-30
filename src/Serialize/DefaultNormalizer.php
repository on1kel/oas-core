<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Serialize;

use On1kel\OAS\Core\Contract\Common\Extensible;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Serialize\Normalizer;

final class DefaultNormalizer implements Normalizer
{
    /** @var list<string> */
    private array $modelNamespaces;

    /**
     * @param list<string> $modelNamespaces Список неймспейсов моделей, которые считаем "своими"
     *                                      (например, ["On1kel\\OAS\\Core\\Model\\"])
     */
    public function __construct(array $modelNamespaces = ['On1kel\\OAS\\Core\\Model\\'])
    {
        $this->modelNamespaces = $modelNamespaces;
    }

    public function supports(object $object): bool
    {
        $cls = $object::class;
        foreach ($this->modelNamespaces as $ns) {
            if (str_starts_with($cls, $ns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string,mixed>
     */
    public function normalize(object $object, SpecProfile $profile): array
    {
        // Коллекции
        if ($this->isList($object)) {
            /** @var iterable<int,mixed> $iter */
            $iter = $object;
            $out = [];
            foreach ($iter as $item) {
                $out[] = $this->normalizeValue($item, $profile);
            }

            return $out;
        }

        if ($this->isMap($object)) {
            /** @var iterable<string,mixed> $iter */
            $iter = $object;
            $out = [];
            foreach ($iter as $k => $item) {
                $out[(string)$k] = $this->normalizeValue($item, $profile);
            }

            return $out;
        }

        // Обычная модель: публичные readonly свойства + extensions
        $data = [];
        /** @var array<string,mixed> $props */
        $props = get_object_vars($object);
        foreach ($props as $name => $value) {
            // пропускаем внутренние поля, если они начинаются с _
            if ($name !== '' && $name[0] === '_') {
                continue;
            }
            $data[$name] = $this->normalizeValue($value, $profile);
        }

        if ($object instanceof Extensible) {
            // метод расширений по контракту; берём «как есть»
            $exts = $object->extensions();
            if (!empty($exts)) {
                foreach ($exts as $k => $v) {
                    $data[$k] = $this->normalizeValue($v, $profile);
                }
            }
        }

        return $data;
    }

    private function isList(object $obj): bool
    {
        // Совместимо с твоей иерархией: BaseList реализует Traversable
        return $obj instanceof \Traversable && method_exists($obj, 'isList') && $obj->isList();
    }

    private function isMap(object $obj): bool
    {
        return $obj instanceof \Traversable && method_exists($obj, 'isMap') && $obj->isMap();
    }

    private function normalizeValue(mixed $value, SpecProfile $profile): mixed
    {
        if (is_object($value)) {
            if ($this->supports($value)) {
                return $this->normalize($value, $profile);
            }
            // Пробуем любые Traversable (например, коллекции без isList/isMap)
            if ($value instanceof \Traversable) {
                $out = [];
                foreach ($value as $k => $v) {
                    $out[$k] = $this->normalizeValue($v, $profile);
                }

                return $out;
            }
            // Фоллбэк — toString-объекты (например, Reference/Uri)
            if (method_exists($value, '__toString')) {
                return (string)$value;
            }

            // Иначе — сырая структура свойств
            return get_object_vars($value);
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->normalizeValue($v, $profile);
            }

            return $out;
        }

        return $value;
    }
}
