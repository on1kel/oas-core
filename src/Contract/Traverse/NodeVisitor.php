<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Traverse;

/**
 * Контракт посетителя для обхода дерева моделей OAS.
 * Walker вызывает enter() перед обходом дочерних узлов и leave() — после.
 * $pointer — валидный JSON Pointer до текущего узла (например, "#/paths/~1pets/get").
 */
interface NodeVisitor
{
    /**
     * Вход в узел.
     *
     * @param string $pointer JSON Pointer текущего узла
     * @param object $node    Объект модели (узел)
     */
    public function enter(string $pointer, object $node): void;

    /**
     * Выход из узла.
     *
     * @param string $pointer JSON Pointer текущего узла
     * @param object $node    Объект модели (узел)
     */
    public function leave(string $pointer, object $node): void;
}
