<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Traverse;

use function array_values;
use function is_array;
use function is_object;

use On1kel\OAS\Core\Contract\Traverse\NodeVisitor;
use ReflectionClass;

use function str_ends_with;
use function str_replace;
use function str_starts_with;

/**
 * Рекурсивный обходчик дерева моделей OAS.
 *
 * Правила:
 *  - Узлами считаются объекты в namespace из опций (по умолчанию On1kel\OAS\Core\Model\).
 *  - В массивы заглядывает, если элементы — объекты-модели или массивы, ведущие к ним.
 *  - Строит путь в формате JSON Pointer (напр. "#/components/schemas/User/properties/name").
 *  - Вызывает у всех посетителей enter($path, $node) перед спуском и leave($path, $node) после.
 *  - Глубина контролируется через maxDepth (если null — без ограничений).
 */
final class Walker
{
    /** @var list<NodeVisitor> */
    private array $visitors;

    private TraverseOptions $options;

    /**
     * @param list<NodeVisitor> $visitors
     */
    public function __construct(array $visitors = [], ?TraverseOptions $options = null)
    {
        $this->visitors = array_values($visitors);
        $this->options  = $options ?? TraverseOptions::defaults();
    }

    /**
     * Добавить посетителя.
     */
    public function addVisitor(NodeVisitor $visitor): void
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Запустить обход с корневого узла модели.
     *
     * @param object      $root        Корневой объект модели (например, OpenApiDocument)
     * @param string|null $basePointer Базовый JSON Pointer (по умолчанию '#')
     */
    public function walk(object $root, ?string $basePointer = null): void
    {
        $base = $basePointer ?? '#';
        $this->walkNode($root, $base, 0);
    }

    /**
     * Обход нескольких корней последовательно.
     *
     * @param iterable<object> $roots
     * @param string|null      $basePointer
     */
    public function walkMany(iterable $roots, ?string $basePointer = null): void
    {
        foreach ($roots as $root) {
            $this->walk($root, $basePointer);
        }
    }

    /**
     * Рекурсивный обход одного узла.
     */
    private function walkNode(object $node, string $pointer, int $depth): void
    {
        if (!$this->isModelObject($node)) {
            return;
        }

        if ($this->options->maxDepth !== null && $depth > $this->options->maxDepth) {
            return;
        }

        // enter
        foreach ($this->visitors as $v) {
            $v->enter($pointer, $node);
        }

        // Спуститься к детям
        $this->walkChildren($node, $pointer, $depth);

        // leave
        foreach ($this->visitors as $v) {
            $v->leave($pointer, $node);
        }
    }

    /**
     * Обход полей/коллекций узла.
     */
    private function walkChildren(object $node, string $pointer, int $depth): void
    {
        $ref = new ReflectionClass($node);

        foreach ($ref->getProperties() as $prop) {
            // Пробуем пропускать extensions, если так задано в опциях
            if ($this->options->skipExtensions && $prop->getName() === 'extensions') {
                continue;
            }

            if (!$prop->isPublic()) {
                $prop->setAccessible(true);
            }
            $value = $prop->getValue($node);

            if ($value === null) {
                continue;
            }

            $childPtr = $this->childPointer($pointer, $prop->getName());

            // Если свойство — объект модели, просто уходим вглубь
            if (is_object($value)) {
                if (!$this->isModelObject($value)) {
                    // Не-модельные объекты пропускаем (DateTime и т.п.)
                    continue;
                }
                // Reference — по флагу
                if (!$this->options->visitReferences && $this->isReferenceObject($value)) {
                    continue;
                }
                $this->walkNode($value, $childPtr, $depth + 1);
                continue;
            }

            // Если массив — обходим элементы
            if (is_array($value)) {
                $this->walkArray($value, $childPtr, $depth + 1);
                continue;
            }

            // Скаляры (int/string/bool/float) пропускаем — посетителям не сигналим
        }
    }

    /**
     * Обход массива: спускаемся туда, где находим объекты моделей или вложенные массивы.
     *
     * @param array<mixed> $arr
     */
    private function walkArray(array $arr, string $pointer, int $depth): void
    {
        foreach ($arr as $key => $val) {
            $childPtr = $this->childPointer($pointer, (string)$key);

            if (is_object($val)) {
                if (!$this->isModelObject($val)) {
                    continue;
                }
                if (!$this->options->visitReferences && $this->isReferenceObject($val)) {
                    continue;
                }
                $this->walkNode($val, $childPtr, $depth);
                continue;
            }

            if (is_array($val)) {
                // Рекурсия в глубину для массивов
                $this->walkArray($val, $childPtr, $depth);
                continue;
            }
        }
    }

    /**
     * Проверка, что объект принадлежит namespace моделей.
     */
    private function isModelObject(object $obj): bool
    {
        return str_starts_with($obj::class, $this->options->modelNamespace);
    }

    /**
     * Эвристика для объектов-ссылок Reference (по имени класса).
     */
    private function isReferenceObject(object $obj): bool
    {
        // Класс может называться по-разному, но обычно заканчивается на "\Reference"
        return str_ends_with($obj::class, '\\Reference') || str_ends_with($obj::class, 'Reference');
    }

    /**
     * Построить JSON Pointer для дочернего узла с экранированием сегмента.
     */
    private function childPointer(string $parent, string $segment): string
    {
        $escaped = str_replace(['~', '/'], ['~0', '~1'], $segment);

        return $parent === '#' ? "#/{$escaped}" : "{$parent}/{$escaped}";
    }
}
