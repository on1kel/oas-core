<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation;

use function array_filter;
use function array_values;
use function count;

use Countable;
use IteratorAggregate;
use Severity;
use Traversable;

/**
 * Итоговый отчёт валидации: коллекция ошибок/варнингов/инфо.
 */
final class ValidationReport implements Countable, IteratorAggregate
{
    /** @var list<ValidationError> */
    private array $items;

    /**
     * @param list<ValidationError> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    public function isOk(): bool
    {
        foreach ($this->items as $e) {
            if ($e->severity === Severity::Error) {
                return false;
            }
        }

        return true;
    }

    /** @return list<ValidationError> */
    public function all(): array
    {
        return $this->items;
    }

    /** @return list<ValidationError> */
    public function errors(): array
    {
        return array_values(array_filter($this->items, fn ($e) => $e->severity === Severity::Error));
    }

    /** @return list<ValidationError> */
    public function warnings(): array
    {
        return array_values(array_filter($this->items, fn ($e) => $e->severity === Severity::Warning));
    }

    /** @return list<ValidationError> */
    public function infos(): array
    {
        return array_values(array_filter($this->items, fn ($e) => $e->severity === Severity::Info));
    }

    /**
     * Группировка по JSON Pointer узлам.
     *
     * @return array<string, list<ValidationError>>
     */
    public function groupByPointer(): array
    {
        $out = [];
        foreach ($this->items as $e) {
            $out[$e->pointer][] = $e;
        }

        return $out;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Создание нового отчёта с дополненными ошибками.
     *
     * @param list<ValidationError> $more
     */
    public function with(array $more): self
    {
        return new self([...$this->items, ...$more]);
    }
}
