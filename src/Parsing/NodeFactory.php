<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Parsing;

/**
 * Фабрика узлов: массив -> доменная сущность.
 * В базовой версии возвращает массив как есть.
 * Подменяется на денормализацию моделей без изменения DocumentParser.
 */
class NodeFactory
{
    /**
     * @param  array<mixed> $data
     * @return mixed        (массив либо уже денормализованная модель)
     */
    public function make(string $nodeKind, array $data, PathContext $ctx): mixed
    {
        // nodeKind — подсказка: 'OpenApiDocument', 'PathItem', 'Schema', ...
        // Здесь можно включить реальную денормализацию моделей.
        return $data;
    }
}
