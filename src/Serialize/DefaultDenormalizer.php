<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Serialize;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Serialize\Denormalizer;

final class DefaultDenormalizer implements Denormalizer
{
    public function __construct(
        private readonly TypeRegistry $registry
    ) {
    }

    public function supports(string $nodeType): bool
    {
        return $this->registry->has($nodeType);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function denormalize(array $data, string $nodeType, SpecProfile $profile): object
    {
        return $this->registry->make($nodeType, $data, $profile);
    }

    /**
     * Удобный helper для полного документа.
     * @return object
     */
    public function fromArray(array $data, SpecProfile $profile): object
    {
        return $this->denormalize($data, 'OpenApiDocument', $profile);
    }

    /**
     * @return object
     */
    public function fromJson(string $json, SpecProfile $profile): object
    {
        /** @var array<string,mixed> $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $this->fromArray($data, $profile);
    }
}
