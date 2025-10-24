<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Validation;

/**
 * Контракт атомарного правила валидации для конкретного узла модели.
 *
 * Реализации должны быть по возможности статeless и чистыми.
 */
interface NodeValidator
{
    /**
     * Проверяет один узел (и/или его поля) и возвращает список найденных проблем.
     *
     * @param string            $path Внутренний путь обходчика (может отличаться от JSON Pointer, служебно)
     * @param object            $node Узел модели
     * @param ValidationContext $ctx  Контекст (профиль, строгость, pointer и т.п.)
     *
     * @return array<int, ValidationError> Список ошибок/предупреждений/инфо; пустой — если проблем нет
     */
    public function validate(string $path, object $node, ValidationContext $ctx): array;
}
