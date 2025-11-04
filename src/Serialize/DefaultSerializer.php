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
     * Рекурсивная чистка пустот с учётом OpenAPI security:
     *  - поднимает узлы-обёртки { requirements: {...}, ...пустое } → в {...}
     *  - НЕ удаляет пустые [] у скоупов security-схем (значимо!)
     *  - удаляет null и пустые [] в остальных местах
     *
     * @param  mixed             $value
     * @param  SpecProfile       $profile
     * @param  array<int,string> $path
     * @param  bool              $preserveScopes Контекст: находимся внутри карты скоупов security-схем
     * @return mixed
     */
    private function pruneEmpty(mixed $value, SpecProfile $profile, array $path = [], bool $preserveScopes = false)
    {
        if (!is_array($value)) {
            return $value;
        }

        // ── 0) Универсально раскрываем "requirements" на уровень выше
        //     Только если остальные поля отсутствуют/пустые (null | [])
        $isAssoc = !array_is_list($value);
        if ($isAssoc && array_key_exists('requirements', $value) && is_array($value['requirements'])) {
            $other = array_diff(array_keys($value), ['requirements']);
            $othersAllEmpty = true;
            foreach ($other as $k) {
                $v = $value[$k] ?? null;
                if ($v !== null && (!is_array($v) || $v !== [])) {
                    $othersAllEmpty = false;
                    break;
                }
            }
            if ($other === [] || $othersAllEmpty) {
                // Переходим в режим "карта скоупов": пустые [] теперь значимы
                return $this->pruneEmpty($value['requirements'], $profile, $path, true);
            }
        }

        $result = [];
        $parentIsList = array_is_list($value);

        foreach ($value as $key => $v) {
            $keyStr = (string)$key;
            $currentPath = [...$path, $keyStr];

            if (is_array($v)) {
                // Наследуем контекст preserveScopes внутрь
                $v = $this->pruneEmpty($v, $profile, $currentPath, $preserveScopes);
            }

            // Явная защита по флагу профиля
            $preserveFlagName = 'preserveEmpty.' . $keyStr;
            if ($profile->features()->extraFlag($preserveFlagName, false)) {
                $result[$key] = $v;
                continue;
            }

            // Удаляем null
            if ($v === null) {
                continue;
            }

            // Пустой массив: решаем по контексту
            if (is_array($v) && $v === []) {
                // 1) Если мы внутри списка — это мусор (кроме режима скоупов)
                if ($parentIsList && !$preserveScopes) {
                    continue;
                }

                // 2) Если мы в контексте скоупов (или явно под security/requirements) — сохраняем
                $underRequirements = in_array('requirements', $path, true);
                $underSecurity     = in_array('security', $path, true);

                if ($preserveScopes || $underRequirements || $underSecurity) {
                    $result[$key] = $v; // [] значим как "нет требуемых скоупов"
                    continue;
                }

                // 3) Иначе — чистим пустые [] внутри объектов
                continue;
            }

            $result[$key] = $v;
        }

        return $result;
    }

}
