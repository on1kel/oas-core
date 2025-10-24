<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Ref;

use JsonException;
use On1kel\OAS\Core\Contract\Ref\RefFetcher;
use On1kel\OAS\Core\Ref\Exception\RefNotFound;
use On1kel\OAS\Core\Ref\Exception\UnsupportedFormatException;
use On1kel\OAS\Core\Ref\Exception\UnsupportedSchemeException;

final class DefaultRefFetcher implements RefFetcher
{
    /**
     * @param  string        $uri
     * @param  string|null   $baseUri
     * @throws JsonException
     * @return array<mixed>
     */
    public function fetch(string $uri, ?string $baseUri = null): array
    {
        // Нормализуем относительно baseUri, если надо
        if ($baseUri !== null && !str_contains($uri, '://') && !str_starts_with($uri, 'file://')) {
            $uri = UriHelper::resolveRelative($baseUri, $uri);
        }

        $parts  = parse_url($uri);
        $scheme = $parts['scheme'] ?? '';

        if ($scheme === 'file') {
            $path = $parts['path'] ?? '';

            return $this->loadLocal($path ?: '');
        }

        if ($scheme === 'http' || $scheme === 'https') {
            return $this->loadHttp($uri);
        }

        if ($scheme === '') {
            return $this->loadLocal($uri);
        }

        throw UnsupportedSchemeException::for($uri);
    }

    /**
     * @param  string        $path
     * @throws JsonException
     * @return array<mixed>
     */
    private function loadLocal(string $path): array
    {
        if ($path === '' || !is_file($path)) {
            throw RefNotFound::for($path, $path);
        }
        $content = file_get_contents($path);
        if ($content === false) {
            throw RefNotFound::for($path, $path);
        }

        return $this->decodeByExtension($content, $path);
    }

    /**
     * @param  string        $uri
     * @throws JsonException
     * @return array<mixed>
     */
    private function loadHttp(string $uri): array
    {
        if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOL)) {
            throw UnsupportedSchemeException::for($uri);
        }
        $content = @file_get_contents($uri);
        if ($content === false) {
            throw RefNotFound::for($uri, $uri);
        }

        return $this->decodeByExtension($content, $uri);
    }

    /**
     * @param  string        $content
     * @param  string        $source
     * @throws JsonException
     * @return array<mixed>
     */
    private function decodeByExtension(string $content, string $source): array
    {
        $lower = strtolower($source);
        if (str_ends_with($lower, '.yaml') || str_ends_with($lower, '.yml')) {
            throw UnsupportedFormatException::for($source);
        }

        /** @var array<mixed> $json */
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return is_array($json) ? $json : [];
    }

}
