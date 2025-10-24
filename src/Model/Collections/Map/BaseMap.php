<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

/**
 * @template TKey of string
 * @template TValue
 *
 * Базовый класс типобезопасных ассоциативных коллекций вида map<string, TValue>.
 * Дочерние классы обязаны реализовать validateItem() и typeLabel() для строгой проверки типа.
 */
abstract class BaseMap implements \IteratorAggregate, \Countable
{
    /** @var array<TKey,TValue> */
    protected array $items;

    /**
     * @param array<TKey,TValue> $items
     */
    final public function __construct(array $items)
    {
        foreach ($items as $key => $value) {
            //            if (!\is_string($key)) {
            //                $t = \gettype($key);
            //                throw new \InvalidArgumentException("Ключ карты должен быть string, получено: {$t}");
            //            }
            if (!$this->validateItem($value)) {
                $t = \is_object($value) ? $value::class : \gettype($value);
                throw new \InvalidArgumentException(
                    "Неверный тип значения для ключа '{$key}': {$t}. Ожидается: " . $this->typeLabel()
                );
            }
        }
        $this->items = $items;
    }

    /**
     * Проверка корректности значения карты (реализует дочерний класс).
     *
     * @param mixed $value
     */
    abstract protected function validateItem(mixed $value): bool;

    /**
     * Текстовая метка ожидаемого типа (для сообщений об ошибках).
     */
    abstract protected function typeLabel(): string;

    /**
     * Есть ли элемент по ключу.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->items);
    }

    /**
     * Получить элемент по ключу.
     *
     * @return TValue|null
     */
    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /**
     * Итератор по элементам.
     *
     * @return \Traversable<TKey,TValue>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    /**
     * Количество элементов.
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * Вернуть «сырое» содержимое карты.
     *
     * @return array<TKey,TValue>
     */
    public function all(): array
    {
        return $this->items;
    }
}
