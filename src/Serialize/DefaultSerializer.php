<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Serialize;

use JsonException;
use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Contract\Serialize\Denormalizer as DenormalizerContract;
use On1kel\OAS\Core\Contract\Serialize\Normalizer as NormalizerContract;
use On1kel\OAS\Core\Contract\Serialize\Serializer as SerializerContract;
use RuntimeException;

final class DefaultSerializer implements SerializerContract
{
    /** @var list<NormalizerContract> */
    private array $normalizers;

    public function __construct(
        array $normalizers,
        private readonly DenormalizerContract $denormalizer
    ) {
        // последний — дефолтный normalizer, если хотите приоритеты менять
        $this->normalizers = array_values($normalizers);
    }

    public function toArray(object $object, SpecProfile $profile): array
    {
        $normalizer = $this->pickNormalizer($object);
        $arr = $normalizer->normalize($object, $profile);

        // Управление очисткой через скалярный флаг в FeatureSet::extra
        $prune = (bool) $profile->features()->extraFlag('pruneEmpty', true);

        return $prune ? $this->pruneEmpty($arr, $profile) : $arr;
    }

    /**
     * @throws JsonException
     */
    public function toJson(object $object, SpecProfile $profile, int $flags = 320): string
    {
        $arr = $this->toArray($object, $profile);

        return json_encode($arr, JSON_THROW_ON_ERROR | $flags);
    }


    public function fromArray(array $data, SpecProfile $profile): object
    {
        return $this->denormalizer->fromArray($data, $profile);
    }

    public function fromJson(string $json, SpecProfile $profile): object
    {
        return $this->denormalizer->fromJson($json, $profile);
    }

    private function pickNormalizer(object $object): NormalizerContract
    {
        foreach ($this->normalizers as $n) {
            if ($n->supports($object)) {
                return $n;
            }
        }
        throw new RuntimeException('No normalizer supports object of class ' . $object::class);
    }

    /**
     * Рекурсивно удаляет пустые значения:
     *  - null
     *  - пустые массивы []
     *
     * Сохраняем 0, '0', false.
     * Можно принудительно сохранить пустое значение для конкретного ключа
     * через флаг extra: 'preserveEmpty.<key>' = true/1.
     *
     * @param  mixed $value
     * @return mixed
     */
    private function pruneEmpty(mixed $value, SpecProfile $profile, array $path = [])
    {
        if (!is_array($value)) {
            return $value;
        }

        $result = [];
        foreach ($value as $key => $v) {
            $keyStr = (string)$key;
            $currentPath = [...$path, $keyStr];

            $v = is_array($v) ? $this->pruneEmpty($v, $profile, $currentPath) : $v;

            // индивидуальный скалярный флаг: preserveEmpty.<key>
            $preserveFlagName = 'preserveEmpty.' . $keyStr;
            $preserve = (bool) $profile->features()->extraFlag($preserveFlagName, false);

            if ($preserve) {
                $result[$key] = $v;
                continue;
            }

            $isEmptyArray = is_array($v) && $v === [];
            if ($v === null || $isEmptyArray) {
                continue; // вычищаем пустоту
            }

            $result[$key] = $v;
        }

        return $result;
    }
}
