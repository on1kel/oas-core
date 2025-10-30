<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Traverse;

/**
 * Опции управления обходом дерева моделей.
 */
final class TraverseOptions
{
    /**
     * @param int|null $maxDepth        Максимальная глубина рекурсии (null = без ограничения).
     * @param bool     $visitReferences Посещать ли узлы-ссылки (объекты Reference в модели).
     * @param string   $modelNamespace  Пространство имён для узлов модели (фильтр типов).
     * @param bool     $skipExtensions  Пропускать ли поля-расширения 'extensions' (x-*) при обходе.
     */
    public function __construct(
        public readonly ?int $maxDepth = null,
        public readonly bool $visitReferences = true,
        public readonly string $modelNamespace = 'On1kel\\OAS\\Core\\Model\\',
        public readonly bool $skipExtensions = true,
    ) {
        if ($this->maxDepth !== null && $this->maxDepth < 0) {
            throw new \InvalidArgumentException('maxDepth должен быть >= 0 или null.');
        }
    }

    public static function defaults(): self
    {
        return new self();
    }

    public function withMaxDepth(?int $depth): self
    {
        return new self($depth, $this->visitReferences, $this->modelNamespace, $this->skipExtensions);
    }

    public function withVisitReferences(bool $flag): self
    {
        return new self($this->maxDepth, $flag, $this->modelNamespace, $this->skipExtensions);
    }

    public function withModelNamespace(string $ns): self
    {
        return new self($this->maxDepth, $this->visitReferences, rtrim($ns, '\\') . '\\', $this->skipExtensions);
    }

    public function withSkipExtensions(bool $flag): self
    {
        return new self($this->maxDepth, $this->visitReferences, $this->modelNamespace, $flag);
    }
}
