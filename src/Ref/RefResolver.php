<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref;

use On1kel\OAS\Core\Contract\Ref\RefCache;
use On1kel\OAS\Core\Contract\Ref\RefFetcher;
use On1kel\OAS\Core\Contract\Ref\RefResolution;
use On1kel\OAS\Core\Ref\Exception\CircularReferenceException;
use On1kel\OAS\Core\Ref\Exception\RefNotFound;

final class RefResolver
{
    public function __construct(
        private readonly RefFetcher $fetcher,
        private readonly RefCache $cache,
        private readonly int $maxDepth = 64,
    ) {
    }

    /**
     * @param array<mixed>|null $rootDoc Сырые данные текущего документа (для локальных фрагментов)
     * @param list<string>      $stack
     */
    public function resolve(string $ref, string $baseUri, ?array $rootDoc = null, array $stack = []): RefResolution
    {
        $cacheKey = UriHelper::makeCacheKey($baseUri, $ref);
        if (($cached = $this->cache->get($cacheKey)) instanceof RefResolution) {
            return $cached;
        }

        if (count($stack) >= $this->maxDepth) {
            throw CircularReferenceException::detected($stack, $ref);
        }

        [$targetUri, $pointer] = UriHelper::splitRef($baseUri, $ref);
        $chainKey = $targetUri . '#' . $pointer;
        if (in_array($chainKey, $stack, true)) {
            throw CircularReferenceException::detected($stack, $chainKey);
        }
        $stack[] = $chainKey;

        // Локальный фрагмент
        if ($targetUri === $baseUri && $rootDoc !== null) {
            $node = $pointer === '' ? $rootDoc : UriHelper::getByPointer($rootDoc, $pointer);
            if (!is_array($node)) {
                throw RefNotFound::for($ref, $baseUri);
            }

            $resolution = new RefResolution(
                originalRef: $ref,
                baseUri:     $baseUri,
                resolvedUri: $targetUri . ($pointer !== '' ? '#' . $pointer : ''),
                pointer:     $pointer,
                data:        $node,
            );
            $this->cache->put($cacheKey, $resolution);

            return $resolution;
        }

        // Внешний документ
        $doc  = $this->fetcher->fetch($targetUri, $baseUri);
        $node = $pointer === '' ? $doc : UriHelper::getByPointer($doc, $pointer);
        if ($node === null || !is_array($node)) {
            throw RefNotFound::for($ref, $baseUri);
        }

        $resolution = new RefResolution(
            originalRef: $ref,
            baseUri:     $targetUri,
            resolvedUri: $targetUri . ($pointer !== '' ? '#' . $pointer : ''),
            pointer:     $pointer,
            data:        $node,
        );
        $this->cache->put($cacheKey, $resolution);

        return $resolution;
    }
}
