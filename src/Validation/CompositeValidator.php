<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Validation;

use On1kel\OAS\Core\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Validation\NodeValidator;
use On1kel\OAS\Core\Contract\Validation\ValidationContext;
use On1kel\OAS\Core\Contract\Validation\ValidationError;
use On1kel\OAS\Core\Contract\Validation\ValidationReport;
use On1kel\OAS\Core\Contract\Validation\Validator;
use Traversable;

final class CompositeValidator implements Validator
{
    /** @var list<NodeValidator> */
    private array $rules;

    /**
     * @param list<NodeValidator> $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = array_values($rules);
    }

    public function with(NodeValidator $rule): self
    {
        $copy = clone $this;
        $copy->rules[] = $rule;

        return $copy;
    }

    public function validate(object $root, SpecProfile $profile, Strictness $strictness = Strictness::Strict, ?string $baseUri = null): ValidationReport
    {
        $ctx = new ValidationContext($profile, $strictness, $baseUri, '#');
        $items = [];
        $this->walk($root, '$', $ctx, $items);

        return new ValidationReport($items);
    }

    /**
     * @param array<int,ValidationError> $bag
     */
    private function walk(mixed $node, string $path, ValidationContext $ctx, array &$bag): void
    {
        if (is_object($node)) {
            foreach ($this->rules as $rule) {
                $errs = $rule->validate($path, $node, $ctx);
                if (!empty($errs)) {
                    array_push($bag, ...$errs);
                }
            }

            /** @var array<string,mixed> $props */
            $props = get_object_vars($node);
            foreach ($props as $name => $value) {
                if ($name !== '' && $name[0] === '_') {
                    continue;
                }
                $this->walk($value, $path . '.' . $name, $ctx->child((string)$name), $bag);
            }

            if ($node instanceof Traversable) {
                foreach ($node as $k => $v) {
                    $seg = (string)$k;
                    $this->walk($v, $path . '[' . $seg . ']', $ctx->child($seg), $bag);
                }
            }

            return;
        }

        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $seg = (string)$k;
                $this->walk($v, $path . '[' . $seg . ']', $ctx->child($seg), $bag);
            }
        }
    }
}
