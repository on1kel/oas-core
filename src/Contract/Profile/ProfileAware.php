<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Contract\Profile;

/**
 * Контракт для компонентов, которым необходим активный профиль версии (SpecProfile).
 * Например, билдерам, сериализаторам или линтерам.
 */
interface ProfileAware
{
    /**
     * Установить активный профиль.
     */
    public function withProfile(SpecProfile $profile): static;

    /**
     * Получить активный профиль.
     */
    public function profile(): SpecProfile;
}
