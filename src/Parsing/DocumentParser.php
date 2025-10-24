<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Parsing;

use On1kel\OAS\Core\Ref\Exception\CircularReferenceException;
use On1kel\OAS\Core\Ref\RefResolver;
use On1kel\OAS\Core\Ref\UriHelper;
use On1kel\OAS\Core\Version\ParseOptions;

final class DocumentParser
{
    public function __construct(
        private readonly RefResolver $resolver,
        private readonly NodeFactory $factory = new NodeFactory(),
        private readonly int $maxWalkDepth = 8192,
    ) {
    }

    /**
     * Парсит корневой документ OAS/JSON Schema в нормализованный вид.
     * - Разрешает $ref (локальные/внешние — в зависимости от опций)
     * - Делает merge с сиблингами (сиблинги имеют приоритет поверх целевого узла)
     * - Возвращает массив, готовый к денормализации в модели
     *
     * @param  array<mixed> $rawDoc
     * @return mixed        (массив или денормализованная модель, если NodeFactory это делает)
     */
    public function parse(array $rawDoc, string $baseUri, ParseOptions $options): mixed
    {
        $ctx = PathContext::root($baseUri);
        $seen = [];
        $data = $this->walkNode($rawDoc, $ctx, $options, 0, $seen, 'OpenApiDocument', $rawDoc);

        // В этот момент $data — нормализованный массив (или модель, если фабрика преобразует)
        return $this->factory->make('OpenApiDocument', $data, $ctx);
    }

    /**
     * @param  array<mixed>|scalar|null $node
     * @param  array<string, true>      $seen
     * @param  array<mixed>             $rootDoc корень текущего документа (для локальных $ref)
     * @return array<mixed>|scalar|null
     */
    private function walkNode(mixed $node, PathContext $ctx, ParseOptions $options, int $depth, array &$seen, string $kind, array $rootDoc): mixed
    {
        if ($depth > $this->maxWalkDepth) {
            throw new \RuntimeException("Traversal depth exceeded at {$ctx->pointer()}");
        }

        // Скаляры или null — возвращаем как есть
        if (!is_array($node)) {
            return $node;
        }

        // Если это "реф-объект": {'$ref': '...'} с возможными сиблингами
        if (array_key_exists('$ref', $node) && is_string($node['$ref'])) {
            $ref = $node['$ref'];

            // внешние $ref можно отключить
            if (!$options->resolveExternalRefs) {
                // При отключённых внешних — пропускаем только локальные (#...)
                [$targetUri,] = UriHelper::splitRef($ctx->baseUri, $ref);
                $isLocal = ($targetUri === $ctx->baseUri) || str_starts_with($ref, '#');
                if (!$isLocal) {
                    // оставляем как есть (или можно бросать исключение — на твой выбор политики)
                    return $node;
                }
            }

            // защита от циклов (ключ по целевому URI#pointer)
            [$targetUri, $pointer] = UriHelper::splitRef($ctx->baseUri, $ref);
            $chainKey = $targetUri . '#' . $pointer;
            if (isset($seen[$chainKey])) {
                throw CircularReferenceException::detected(array_keys($seen), $chainKey);
            }
            $seen[$chainKey] = true;

            // Разрешаем
            $resolution = $this->resolver->resolve($ref, $ctx->baseUri, $rootDoc /* локальные фрагменты */, array_keys($seen));

            // Меняем baseUri, если прыгнули во внешний документ
            $nextBase = $resolution->baseUri ?? $ctx->baseUri;
            $resolvedDocBaseCtx = $ctx->withBaseUri($nextBase);

            // Рекурсивно обходим разрешённый узел
            $resolved = $this->walkNode($resolution->data, $resolvedDocBaseCtx, $options, $depth + 1, $seen, $kind, $resolution->data);

            // Удаляем $ref из сиблингов и мержим: сиблинги поверх таргета
            $siblings = $node;
            unset($siblings['$ref']);

            if (is_array($resolved)) {
                $merged = $this->mergeObjects($resolved, $siblings);
                unset($seen[$chainKey]);

                return $merged;
            }

            // если resolved получился скаляром, а сиблинги есть — это конфликт, вернём resolved как есть
            unset($seen[$chainKey]);

            return $resolved;
        }

        // Обычный объект/массив — обходим рекурсивно
        $result = [];
        // Ассоциативный или список?
        $isList = $this->isList($node);

        if ($isList) {
            $out = [];
            foreach ($node as $i => $item) {
                $out[] = $this->walkNode($item, $ctx->push((string)$i), $options, $depth + 1, $seen, $kind, $rootDoc);
            }

            return $out;
        }

        foreach ($node as $k => $v) {
            $result[$k] = $this->walkNode($v, $ctx->push((string)$k), $options, $depth + 1, $seen, $this->guessKind($k, $kind), $rootDoc);
        }

        return $result;
    }

    /**
     * Слияние объектов: правый массив (сиблинги) перекрывает левый (резолв таргета),
     * при этом вложенные объекты мержатся глубоко, списки — заменяются целиком (OpenAPI-совместно).
     *
     * @param  array<mixed> $left
     * @param  array<mixed> $right
     * @return array<mixed>
     */
    private function mergeObjects(array $left, array $right): array
    {
        foreach ($right as $k => $v) {
            if (array_key_exists($k, $left) && is_array($left[$k]) && is_array($v) && !$this->isList($left[$k]) && !$this->isList($v)) {
                $left[$k] = $this->mergeObjects($left[$k], $v);
            } else {
                $left[$k] = $v;
            }
        }

        return $left;
    }

    /**
     * @param array<mixed> $a
     */
    private function isList(array $a): bool
    {
        if ($a === []) {
            return false;
        }
        $i = 0;
        foreach ($a as $k => $_) {
            if ($k !== $i) {
                return false;
            }
            $i++;
        }

        return true;
    }

    /**
     * Грубая эвристика именования типа узла (подсказка фабрике).
     */
    private function guessKind(string $key, string $parentKind): string
    {
        return match (true) {
            $key === 'paths' => 'Paths',
            $key === 'components' => 'Components',
            $key === 'schemas' && $parentKind === 'Components' => 'SchemaMap',
            $key === 'parameters' && $parentKind === 'Components' => 'ParameterMap',
            $key === 'responses' && $parentKind === 'Components' => 'ResponseMap',
            default => $parentKind,
        };
    }
}
