<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation\Rule;

use On1kel\OAS\Core\Contract\Validation\Enum\Severity;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Model\PathItem;

final class PathTemplateParametersRule implements NodeValidator
{
    public function validate(string $path, object $node, ValidationContext $ctx): array
    {
        if (!is_a($node, PathItem::class)) {
            return [];
        }

        $pointer = $ctx->pointer();
        // Ждём вид: "#/paths/~1pets/~1{petId}"
        $pathStr = $this->extractPathFromPointer($pointer);
        if ($pathStr === null) {
            return [];
        }

        $templateVars = $this->extractTemplateVars($pathStr);
        if ($templateVars === []) {
            return [];
        }

        // Собираем параметры из PathItem и из каждой Operation
        $errors = [];

        $pathParams = $this->collectParameters($node, 'parameters');

        $ops = ['get','put','post','delete','options','head','patch','trace'];
        foreach ($ops as $opName) {
            if (!property_exists($node, $opName) || !is_object($node->{$opName})) {
                continue;
            }

            $op = $node->{$opName};
            $opParams = $this->collectParameters($op, 'parameters');
            $merged = $this->mergeParams($pathParams, $opParams); // op перекрывает pathItem

            foreach ($templateVars as $var) {
                $decl = $merged[$var] ?? null;
                if ($decl === null) {
                    $errors[] = new ValidationError(
                        pointer: $pointer,
                        code: 'path.params.missing',
                        message: "Path variable '{$var}' must be declared as in=path parameter.",
                        severity: Severity::Error
                    );
                    continue;
                }
                if (($decl['in'] ?? null) !== 'path' || ($decl['required'] ?? null) !== true) {
                    $errors[] = new ValidationError(
                        pointer: $pointer,
                        code: 'path.params.invalid',
                        message: "Path parameter '{$var}' must have in='path' and required=true.",
                        severity: Severity::Error
                    );
                }
            }
        }

        return $errors;
    }

    private function extractPathFromPointer(string $pointer): ?string
    {
        // ожидаем "#/paths/<escaped segments...>"
        if (!str_starts_with($pointer, '#/paths/')) {
            return null;
        }
        $seg = substr($pointer, strlen('#/paths/'));
        // берём только первый сегмент пути (сам шаблон), без имени операции/поля
        $firstSlash = strpos($seg, '/');
        $p = $firstSlash === false ? $seg : substr($seg, 0, $firstSlash);
        // unescape ~1 -> '/', ~0 -> '~'
        $p = str_replace(['~1', '~0'], ['/', '~'], $p);

        return $p === '' ? null : $p;
    }

    /**
     * @return list<string>
     */
    private function extractTemplateVars(string $path): array
    {
        preg_match_all('/\{([A-Za-z0-9_.\-]+)\}/', $path, $m);
        /** @var list<string> $vars */
        $vars = $m[1] ?? [];

        return array_values(array_unique($vars));
    }

    /**
     * @return array<string,array{in?:string,required?:bool}>
     */
    private function collectParameters(object $owner, string $prop): array
    {
        $result = [];
        if (!property_exists($owner, $prop)) {
            return $result;
        }
        $params = $owner->{$prop};
        if ($params instanceof \Traversable) {
            foreach ($params as $p) {
                $name = property_exists($p, 'name') ? (string)$p->name : null;
                $in   = property_exists($p, 'in') ? (string)$p->in : null;
                $req  = property_exists($p, 'required') ? (bool)$p->required : null;
                if ($name !== null) {
                    $result[$name] = ['in' => $in, 'required' => $req];
                }
            }
        } elseif (is_array($params)) {
            foreach ($params as $p) {
                if (!is_object($p)) {
                    continue;
                }
                $name = property_exists($p, 'name') ? (string)$p->name : null;
                $in   = property_exists($p, 'in') ? (string)$p->in : null;
                $req  = property_exists($p, 'required') ? (bool)$p->required : null;
                if ($name !== null) {
                    $result[$name] = ['in' => $in, 'required' => $req];
                }
            }
        }

        return $result;
    }

    /**
     * op-параметры должны перекрывать pathItem-параметры по имени.
     * @param  array<string,array{in?:string,required?:bool}> $a
     * @param  array<string,array{in?:string,required?:bool}> $b
     * @return array<string,array{in?:string,required?:bool}>
     */
    private function mergeParams(array $a, array $b): array
    {
        foreach ($b as $name => $meta) {
            $a[$name] = $meta;
        }

        return $a;
    }
}
