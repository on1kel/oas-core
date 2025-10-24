<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\List;

/**
 * @template TItem
 *
 * База для типобезопасных списков (list<TItem>).
 */
abstract class BaseList implements \IteratorAggregate, \Countable
{
    /** @var list<TItem> */
    protected array $items;

    /**
     * @param list<TItem> $items
     */
    final public function __construct(array $items)
    {
        foreach ($items as $i => $value) {
            if (!$this->validateItem($value)) {
                $t = \is_object($value) ? $value::class : \gettype($value);
                throw new \InvalidArgumentException(
                    "Неверный тип значения для индекса {$i}: {$t}. Ожидается: " . $this->typeLabel()
                );
            }
        }
        $this->items = \array_values($items);
    }

    abstract protected function validateItem(mixed $value): bool;
    abstract protected function typeLabel(): string;

    /**
     * @return \Traversable<int,TItem>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @return list<TItem>
     */
    public function all(): array
    {
        return $this->items;
    }
}
