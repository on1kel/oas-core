<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Version;

use On1kel\OAS\Core\Contract\Profile\SpecProfile;
use On1kel\OAS\Core\Version\Exception\ProfileNotFound;

/**
 * Реестр профилей (например, '3.1', '3.2').
 * Позволяет получить профиль по идентификатору и управлять дефолтным.
 */
final class ProfileRegistry
{
    /** @var array<string, SpecProfile> */
    private array $profiles = [];

    private ?string $defaultId = null;

    public function __construct(SpecProfile ...$profiles)
    {
        foreach ($profiles as $p) {
            $this->register($p);
        }
    }

    public function register(SpecProfile $profile, bool $asDefault = false): void
    {
        $id = $profile->majorMinor(); // ожидается, что SpecProfile::id(): string возвращает '3.1'/'3.2'
        $this->profiles[$id] = $profile;
        if ($asDefault || $this->defaultId === null) {
            $this->defaultId = $id;
        }
    }

    public function has(string $id): bool
    {
        return isset($this->profiles[$id]);
    }

    public function get(string $id): SpecProfile
    {
        if (!isset($this->profiles[$id])) {
            throw ProfileNotFound::byId($id);
        }

        return $this->profiles[$id];
    }

    /**
     * Вернёт дефолтный профиль (первый зарегистрированный или явно установленный).
     */
    public function getDefault(): SpecProfile
    {
        if ($this->defaultId === null) {
            throw new ProfileNotFound('Профиль по умолчанию не настроен');
        }

        return $this->profiles[$this->defaultId];
    }

    public function setDefault(string $id): void
    {
        if (!isset($this->profiles[$id])) {
            throw ProfileNotFound::byId($id);
        }
        $this->defaultId = $id;
    }

    /**
     * @return string[]
     */
    public function ids(): array
    {
        return array_keys($this->profiles);
    }

    /**
     * @return list<SpecProfile>
     */
    public function all(): array
    {
        return array_values($this->profiles);
    }
}
